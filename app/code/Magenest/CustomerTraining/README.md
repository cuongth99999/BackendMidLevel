# Magenest_CustomerTraining

Module CRUD cho bảng `customer_training` được lưu trong một database **tách biệt** (`well_trained`)
khác với database mặc định của Magento. Đính kèm UI admin, REST API, GraphQL API và bản dịch theo `storeCode`.

---

## 1. Tính năng

- **Database ngoài**: bảng `customer_training` sống ở DB `well_trained`, không nằm trong DB chính của Magento.
- **Admin UI**: menu "Customer Training → Manage Customers" với listing (filter/sort/mass-delete) và form (Add/Edit/Delete).
- **Service contract**: `CustomerTrainingRepositoryInterface` với `save`, `getById`, `getList`, `delete`, `deleteById`.
- **REST API**: 5 endpoint dưới `/V1/magenest-customer-training/...`.
- **GraphQL API**: query `customerTraining`, `customerTrainings`; mutation `saveCustomerTraining`, `deleteCustomerTraining`.
- **i18n theo storeCode**: tất cả message dùng `__()`; ví dụ bản dịch tại `i18n/vi_VN.csv`.

---

## 2. Cấu trúc thư mục

```
Magenest/CustomerTraining/
├── Api/
│   ├── CustomerTrainingRepositoryInterface.php
│   └── Data/
│       ├── CustomerTrainingInterface.php
│       └── CustomerTrainingSearchResultsInterface.php
├── Block/Adminhtml/Customer/Edit/         # Buttons (Save, Delete, Back, ...)
├── Controller/Adminhtml/Customer/         # Index, NewAction, Edit, Save, Delete, MassDelete
├── Model/
│   ├── CustomerTraining.php               # Model entity
│   ├── CustomerTrainingRepository.php     # Repository implementation
│   ├── CustomerTrainingSearchResults.php  # SearchResults type
│   ├── DataProvider/CustomerDataProvider.php
│   ├── Resolver/                          # GraphQL resolvers
│   │   ├── Query/Get.php
│   │   ├── Query/GetList.php
│   │   ├── Mutation/Save.php
│   │   └── Mutation/Delete.php
│   └── ResourceModel/
│       ├── CustomerTraining.php
│       └── CustomerTraining/Collection.php
├── Setup/Patch/Schema/CreateCustomerTrainingTable.php
├── Ui/Component/Listing/Column/CustomerActions.php
├── etc/
│   ├── acl.xml
│   ├── adminhtml/{routes.xml, menu.xml}
│   ├── di.xml
│   ├── module.xml
│   ├── schema.graphqls
│   └── webapi.xml
├── i18n/vi_VN.csv
├── view/adminhtml/                        # layout + ui_component
├── registration.php
└── README.md
```

---

## 3. ⚙️ Khai báo database ngoài (PHẦN QUAN TRỌNG)

Declarative schema (`db_schema.xml`) **không hỗ trợ** custom resource — XSD chỉ cho phép
`{default, checkout, sales}`. Để dùng DB tách biệt cần làm đầy đủ **3 bước** sau:

### Bước 3.1 — Khai báo connection trong `app/etc/env.php`

```php
'db' => [
    'table_prefix' => '',
    'connection' => [
        'default' => [ /* ... DB Magento mặc định ... */ ],

        // CONNECTION MỚI
        'well_trained' => [
            'host' => 'localhost',
            'dbname' => 'well_trained',
            'username' => 'dev',
            'password' => '1',
            'model' => 'mysql4',
            'engine' => 'innodb',
            'initStatements' => 'SET NAMES utf8;',
            'active' => '1',
        ],
    ],
],
'resource' => [
    'default_setup' => ['connection' => 'default'],
    // MAPPING RESOURCE → CONNECTION
    'well_trained' => ['connection' => 'well_trained'],
],
```

### Bước 3.2 — Tạo bảng bằng Schema Patch (KHÔNG dùng db_schema.xml)

Vì declarative schema không nhận resource lạ, ta tạo bảng bằng DDL thông qua một
`SchemaPatchInterface`:

```php
// Setup/Patch/Schema/CreateCustomerTrainingTable.php
class CreateCustomerTrainingTable implements SchemaPatchInterface, PatchRevertableInterface
{
    public function __construct(private readonly ResourceConnection $resourceConnection) {}

    public function apply(): self
    {
        // 👇 LẤY CONNECTION TÊN well_trained
        $connection = $this->resourceConnection->getConnection('well_trained');

        if ($connection->isTableExists('customer_training')) {
            return $this;
        }

        $table = $connection->newTable('customer_training')
            ->addColumn('entity_id', Table::TYPE_INTEGER, null, [
                'identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true,
            ], 'Entity ID')
            ->addColumn('first_name', Table::TYPE_TEXT, 255, ['nullable' => false], 'First Name')
            // ... các cột khác
            ->setComment('Customer Training (well_trained database)');

        $connection->createTable($table);
        return $this;
    }

    public function revert(): void { /* drop table */ }
    public static function getDependencies(): array { return []; }
    public function getAliases(): array { return []; }
}
```

Sau đó chạy `php bin/magento setup:upgrade` — Magento tự apply patch và tạo bảng
trong DB `well_trained`.

### Bước 3.3 — Gắn connection vào ResourceModel qua `di.xml`

ResourceModel cần biết phải đọc/ghi qua connection nào:

```xml
<!-- etc/di.xml -->
<type name="Magenest\CustomerTraining\Model\ResourceModel\CustomerTraining">
    <arguments>
        <argument name="connectionName" xsi:type="string">well_trained</argument>
    </arguments>
</type>
```

ResourceModel chỉ cần khai báo bình thường:

```php
class CustomerTraining extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('customer_training', 'entity_id');
    }
}
```

Với 3 bước trên, mọi `save()`, `load()`, query Collection... đều đi vào `well_trained`,
không đụng vào DB Magento mặc định.

> ⚠️ **Khi cần cho UI Grid (Admin listing)**: virtualType `SearchResult` cũng phải
> truyền `connectionName="well_trained"` (xem trong `etc/di.xml`).

---

## 4. Lấy data ra Collection

Model + ResourceModel + Collection theo pattern Magento chuẩn:

```php
// Model/ResourceModel/CustomerTraining/Collection.php
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(
            \Magenest\CustomerTraining\Model\CustomerTraining::class,
            \Magenest\CustomerTraining\Model\ResourceModel\CustomerTraining::class
        );
    }
}
```

Vì connection đã được bind vào ResourceModel qua `di.xml`, Collection **tự động** dùng
đúng DB `well_trained`. Không cần override gì thêm.

### Cách dùng Collection

```php
public function __construct(
    private readonly \Magenest\CustomerTraining\Model\ResourceModel\CustomerTraining\CollectionFactory $collectionFactory
) {}

public function example(): void
{
    $collection = $this->collectionFactory->create();
    $collection
        ->addFieldToFilter('city', 'Anaheim')
        ->addFieldToFilter('age', ['gteq' => 18])
        ->addOrder('age', 'DESC')
        ->setPageSize(10)
        ->setCurPage(1);

    foreach ($collection as $item) {
        // $item là \Magenest\CustomerTraining\Model\CustomerTraining
        $item->getFirstName();
    }
}
```

### Cách dùng qua Repository (khuyến nghị)

```php
public function __construct(
    private readonly \Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface $repository,
    private readonly \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
) {}

public function example(): array
{
    $criteria = $this->searchCriteriaBuilder
        ->addFilter('city', 'Anaheim', 'eq')
        ->addFilter('age', 18, 'gteq')
        ->setPageSize(10)
        ->setCurrentPage(1)
        ->create();

    $result = $this->repository->getList($criteria);
    return $result->getItems();           // CustomerTrainingInterface[]
    // $result->getTotalCount() — tổng số bản ghi không tính phân trang
}
```

---

## 5. Cách tạo REST API

REST API trong Magento chỉ cần **3 phần**:

1. Một **service contract interface** trong `Api/` (có PHPDoc `@param`/`@return`/`@throws` đầy đủ).
2. Một **implementation class** với `<preference>` trong `etc/di.xml`.
3. File **`etc/webapi.xml`** map URL → service method.

### 5.1 — Service contract

```php
// Api/CustomerTrainingRepositoryInterface.php
interface CustomerTrainingRepositoryInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magenest\CustomerTraining\Api\Data\CustomerTrainingSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): CustomerTrainingSearchResultsInterface;
    // ... save, getById, delete, deleteById
}
```

> ⚠️ **PHPDoc bắt buộc.** WebAPI introspection đọc docblock để build metadata.
> Thiếu `@return` sẽ lỗi `Method's return type must be specified using @return annotation`.

### 5.2 — `etc/webapi.xml`

```xml
<routes xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Webapi:etc/webapi.xsd">

    <route url="/V1/magenest-customer-training/search" method="GET">
        <service class="Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface" method="getList"/>
        <resources><resource ref="anonymous"/></resources>
    </route>

    <route url="/V1/magenest-customer-training/:entityId" method="GET">
        <service class="Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface" method="getById"/>
        <resources><resource ref="anonymous"/></resources>
    </route>

    <route url="/V1/magenest-customer-training" method="POST">
        <service class="Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface" method="save"/>
        <resources><resource ref="anonymous"/></resources>
    </route>

    <route url="/V1/magenest-customer-training/:id" method="PUT">
        <service class="Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface" method="save"/>
        <resources><resource ref="anonymous"/></resources>
    </route>

    <route url="/V1/magenest-customer-training/:entityId" method="DELETE">
        <service class="Magenest\CustomerTraining\Api\CustomerTrainingRepositoryInterface" method="deleteById"/>
        <resources><resource ref="anonymous"/></resources>
    </route>
</routes>
```

`resource="anonymous"` = ai cũng gọi được; production nên đổi thành ACL resource
(VD `Magenest_CustomerTraining::customer_training_manage`) và yêu cầu admin token.

### 5.3 — `SearchResults` cụ thể (tránh lỗi return type)

Magento tự inject một factory cho `CustomerTrainingSearchResultsInterface`. Vì
preference không thể trỏ thẳng vào `Magento\Framework\Api\SearchResults` (class này
không `implements` interface của bạn), ta tạo class con:

```php
// Model/CustomerTrainingSearchResults.php
class CustomerTrainingSearchResults extends \Magento\Framework\Api\SearchResults
    implements CustomerTrainingSearchResultsInterface {}
```

```xml
<preference for="Magenest\CustomerTraining\Api\Data\CustomerTrainingSearchResultsInterface"
            type="Magenest\CustomerTraining\Model\CustomerTrainingSearchResults"/>
```

---

## 6. 🔍 Đặc biệt: `getList` (REST) với SearchCriteria

Magento serialize `SearchCriteriaInterface` thành query string với cấu trúc cố định.
Đây là phần dễ gây nhầm — dưới đây là toàn bộ tham chiếu.

### 6.1 — Cấu trúc query string

```
searchCriteria[currentPage]=<int>
searchCriteria[pageSize]=<int>

# Sort (nhiều cột)
searchCriteria[sortOrders][0][field]=<column>
searchCriteria[sortOrders][0][direction]=ASC|DESC

# Filter:
#   filter_groups[N]          = AND giữa các group
#   filters[M] trong 1 group  = OR
searchCriteria[filter_groups][0][filters][0][field]=<column>
searchCriteria[filter_groups][0][filters][0][value]=<value>
searchCriteria[filter_groups][0][filters][0][condition_type]=eq|neq|like|in|gt|lt|gteq|lteq|null|notnull|finset
```

### 6.2 — Lệnh `curl` ví dụ

**Get list mặc định** (20 record đầu):

```bash
curl -s "http://backendmidlevel.local.com/rest/V1/magenest-customer-training/search?\
searchCriteria[currentPage]=1&searchCriteria[pageSize]=20"
```

**Phân trang 5/page, trang 2**:

```bash
curl -s "http://backendmidlevel.local.com/rest/V1/magenest-customer-training/search?\
searchCriteria[currentPage]=2&searchCriteria[pageSize]=5"
```

**Filter `city = Anaheim`**:

```bash
curl -s -G "http://backendmidlevel.local.com/rest/V1/magenest-customer-training/search" \
  --data-urlencode "searchCriteria[filter_groups][0][filters][0][field]=city" \
  --data-urlencode "searchCriteria[filter_groups][0][filters][0][value]=Anaheim" \
  --data-urlencode "searchCriteria[filter_groups][0][filters][0][condition_type]=eq"
```

**Filter `last_name LIKE '%Mouse%'` AND `age >= 18`** (mỗi điều kiện AND nằm trong group riêng):

```bash
curl -s -G "http://backendmidlevel.local.com/rest/V1/magenest-customer-training/search" \
  --data-urlencode "searchCriteria[filter_groups][0][filters][0][field]=last_name" \
  --data-urlencode "searchCriteria[filter_groups][0][filters][0][value]=%Mouse%" \
  --data-urlencode "searchCriteria[filter_groups][0][filters][0][condition_type]=like" \
  --data-urlencode "searchCriteria[filter_groups][1][filters][0][field]=age" \
  --data-urlencode "searchCriteria[filter_groups][1][filters][0][value]=18" \
  --data-urlencode "searchCriteria[filter_groups][1][filters][0][condition_type]=gteq"
```

**Sort theo `age` DESC rồi `last_name` ASC**:

```bash
curl -s -G "http://backendmidlevel.local.com/rest/V1/magenest-customer-training/search" \
  --data-urlencode "searchCriteria[sortOrders][0][field]=age" \
  --data-urlencode "searchCriteria[sortOrders][0][direction]=DESC" \
  --data-urlencode "searchCriteria[sortOrders][1][field]=last_name" \
  --data-urlencode "searchCriteria[sortOrders][1][direction]=ASC"
```

**Message lỗi tiếng Việt — thêm storeCode vào URL**:

```bash
curl -s "http://backendmidlevel.local.com/rest/vi/V1/magenest-customer-training/999999"
```

Nếu store `vi` có locale `vi_VN`, Magento tự dùng `i18n/vi_VN.csv` và trả về:

```json
{ "message": "Bản ghi customer training với ID \"999999\" không tồn tại." }
```

### 6.3 — Format response

```json
{
  "items": [
    {
      "entity_id": 1,
      "first_name": "Mickey",
      "last_name": "Mouse",
      "address": "123 Fantasy Way",
      "city": "Anaheim",
      "age": 73,
      "created_at": "2026-05-13 09:21:00",
      "updated_at": "2026-05-13 09:21:00"
    }
  ],
  "search_criteria": {
    "filter_groups": [
      {
        "filters": [
          { "field": "city", "value": "Anaheim", "condition_type": "eq" }
        ]
      }
    ],
    "page_size": 20,
    "current_page": 1
  },
  "total_count": 1
}
```

- `items` — page kết quả (đã filter / sort / paginate).
- `total_count` — tổng số bản ghi match filter (KHÔNG tính phân trang).
- `search_criteria` — echo lại criteria đã dùng, hữu ích để debug.

### 6.4 — POST body (`save`)

```bash
curl -s -X POST "http://backendmidlevel.local.com/rest/V1/magenest-customer-training" \
  -H "Content-Type: application/json" \
  -d '{
    "entity": {
      "first_name": "Mickey",
      "last_name":  "Mouse",
      "address":    "123 Fantasy Way",
      "city":       "Anaheim",
      "age":        73
    }
  }'
```

Key `entity` phải khớp tên parameter trong service method (`save(CustomerTrainingInterface $entity)`).

---

## 7. GraphQL (tóm tắt)

Endpoint: `POST /graphql`. StoreCode truyền qua HTTP header `Store: <code>`.

```graphql
query {
  customerTrainings(
    filter: { city: { eq: "Anaheim" } }
    pageSize: 10
    currentPage: 1
  ) {
    total_count
    items { entity_id first_name last_name city age }
    page_info { page_size current_page total_pages }
  }
}

mutation {
  saveCustomerTraining(input: {
    first_name: "Mickey", last_name: "Mouse",
    address: "123 Fantasy Way", city: "Anaheim", age: 73
  }) { entity_id first_name }
}

mutation {
  deleteCustomerTraining(entity_id: 1) { success message }
}
```

---

## 8. Dịch message theo `storeCode`

- **REST**: storeCode lấy từ URL prefix `/rest/{storeCode}/V1/...`.
- **GraphQL**: storeCode lấy từ HTTP header `Store: <code>`.
- Tất cả message dùng `__()`; Magento tự load `i18n/<locale>.csv` ứng với locale của store.
- Ví dụ bản dịch: `i18n/vi_VN.csv` (Tiếng Việt).
- Thêm ngôn ngữ khác: tạo file `i18n/<locale>.csv` mới (ví dụ `ja_JP.csv`).

> Để dịch thực sự xuất hiện, store cần được tạo với code phù hợp (`Stores → All Stores`)
> và locale phải set đúng (`Stores → Configuration → General → Locale`).

---

## 9. Lệnh CLI cần chạy

```bash
# Sau khi cài/bật module hoặc thay đổi schema
php bin/magento setup:upgrade

# Sau khi sửa di.xml, webapi.xml, schema.graphqls, interface
php bin/magento setup:di:compile

# Sau khi sửa UI/JS/template admin
php bin/magento setup:static-content:deploy -f adminhtml

# Sau mọi thay đổi
php bin/magento cache:clean
php bin/magento cache:flush
```

---

## 10. Kiểm tra dữ liệu thực sự ở DB ngoài

```bash
mysql -u dev -p1 well_trained -e "SELECT * FROM customer_training;"
# Và bảng KHÔNG được tạo trong DB Magento:
mysql -u dev -p1 backend_mid_level -e "SHOW TABLES LIKE 'customer_training';"   # phải trả empty
```
