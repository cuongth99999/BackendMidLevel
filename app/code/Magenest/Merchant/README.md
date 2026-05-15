# Magenest_Merchant

Magento 2.4.8 module quản lý một thực thể "Merchant" độc lập (không phải sản phẩm, không phải customer) cùng với các điểm tích hợp đi kèm với Catalog, CatalogSearch và Customer.

---

## Mục lục

- [Phạm vi chức năng](#phạm-vi-chức-năng)
- [Cấu trúc module](#cấu-trúc-module)
- [Cài đặt / Upgrade](#cài-đặt--upgrade)
- [EAV Entity Type mới: `magenest_merchant`](#eav-entity-type-mới-magenest_merchant) ← phần trọng tâm
- [Exam 1 — Quản lý Merchant (Grid + Form + Seed)](#exam-1--quản-lý-merchant)
- [Exam 2 — Product attribute `merchant` + Elasticsearch search](#exam-2--product-attribute-merchant--elasticsearch-search)
- [Exam 3 — Customer attribute + Mass-assign 3k-5k](#exam-3--customer-attribute--mass-assign-3k-5k)
- [Lưu ý quan trọng & cạm bẫy gặp phải](#lưu-ý-quan-trọng--cạm-bẫy-gặp-phải)

---

## Phạm vi chức năng

| Exam | Mô tả |
|---|---|
| 1 | Tạo một **EAV entity type mới** `magenest_merchant` với 15 attribute (Merchant ID, MC's Phone, Store Name, Category multi-select, Active/Update/Onboarding Date, Status, KYC Level, Merchant Type, Partner, DSA's Phone, City/District/Ward). Admin Grid + Form CRUD đầy đủ. Seed 10 bản ghi mẫu. |
| 2 | Tạo **product attribute** `merchant` (select), source là danh sách merchant. Storefront search có thể tìm product theo **store name** của merchant. Khi đổi store name → reindex **target** chỉ những product có merchant đó. |
| 3 | Tạo **customer attribute** `merchant_id`. **Massaction** mass-assign merchant cho 3k-5k customer mà KHÔNG dùng cron/queue. |

---

## Cấu trúc module

```
app/code/Magenest/Merchant/
├── Api/
│   ├── Data/MerchantInterface.php
│   ├── Data/MerchantSearchResultsInterface.php
│   └── MerchantRepositoryInterface.php
├── Block/Adminhtml/
│   ├── Customer/MassAssignMerchant.php             (Exam 3 view block)
│   └── Form/{Back,Delete,Generic,Save,SaveAndContinue}Button.php
├── Controller/Adminhtml/
│   ├── Merchant.php                                 (ACL parent class)
│   ├── Merchant/{Index,NewAction,Edit,Save,Delete,MassDelete,InlineEdit}.php
│   └── Customer/MassAssignMerchant/{Edit,Save}.php  (Exam 3)
├── Model/
│   ├── Merchant.php                                 (entity model)
│   ├── MerchantRepository.php
│   ├── Merchant/DataProvider.php                    (admin form data)
│   ├── ResourceModel/Merchant.php                   (EAV AbstractEntity)
│   ├── ResourceModel/Merchant/Collection.php
│   ├── ResourceModel/Merchant/Grid/Collection.php
│   └── Source/{Category,City,District,Ward,Status}.php
│   └── Source/Product/MerchantOptions.php           (Exam 2)
│   └── Source/Customer/MerchantOptions.php          (Exam 3)
├── Plugin/InvalidateCatalogSearchOnMerchantSave.php (Exam 2)
├── Setup/Patch/Data/
│   ├── CreateMerchantEntity.php                     (Exam 1)
│   ├── AddMerchantAttributes.php                    (Exam 1)
│   ├── AddSampleMerchants.php                       (Exam 1)
│   ├── RepairSampleMerchants.php                    (Exam 1)
│   ├── AddMerchantProductAttribute.php              (Exam 2)
│   └── AddMerchantCustomerAttribute.php             (Exam 3)
├── Ui/
│   ├── Component/Listing/Column/MerchantActions.php
│   └── DataProvider/MerchantListing.php             (EAV grid data provider)
├── etc/
│   ├── module.xml
│   ├── di.xml
│   ├── acl.xml
│   ├── db_schema.xml + db_schema_whitelist.json
│   └── adminhtml/{menu.xml,routes.xml}
└── view/adminhtml/
    ├── layout/...
    ├── templates/customer/massassign.phtml
    └── ui_component/
        ├── magenest_merchant_merchant_listing.xml   (Exam 1 grid)
        ├── magenest_merchant_merchant_form.xml      (Exam 1 form)
        └── customer_listing.xml                     (Exam 3 massaction)
```

---

## Cài đặt / Upgrade

```bash
php bin/magento module:enable Magenest_Merchant
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
php bin/magento indexer:reindex catalogsearch_fulltext   # nếu đã có product gán merchant
```

---

## EAV Entity Type mới: `magenest_merchant`

Đây là phần kiến trúc trung tâm. Module đăng ký một **EAV entity type độc lập** — không kế thừa `catalog_product` / `customer`, mà là một type cùng level với chúng.

### 1. EAV là gì và tại sao dùng

EAV (Entity-Attribute-Value) là mô hình lưu mỗi thuộc tính của entity vào một dòng riêng trong bảng giá trị (chia theo kiểu dữ liệu). Ngược lại với "flat table" (mỗi attribute là một column).

| Flat table | EAV |
|---|---|
| Thêm attribute → cần `ALTER TABLE` | Thêm attribute → chỉ INSERT vào `eav_attribute` |
| Schema cố định | Schema động, biến theo cấu hình runtime |
| Đọc đơn giản (1 SELECT) | Đọc cần JOIN nhiều bảng value |
| Phù hợp khi attribute set ổn định | Phù hợp khi mỗi entity instance có thể có set attribute khác nhau |

Magento dùng EAV cho `catalog_product`, `catalog_category`, `customer`, `customer_address`. Module này thêm một type mới `magenest_merchant` theo cùng pattern.

### 2. Cấu trúc bảng

```
magenest_merchant_entity              ← bảng entity chính (1 dòng = 1 merchant)
 ├─ entity_id (PK)
 ├─ entity_type_id     → trỏ về eav_entity_type
 ├─ attribute_set_id   → trỏ về eav_attribute_set
 ├─ store_id
 ├─ created_at / updated_at

magenest_merchant_entity_varchar      ← giá trị kiểu chuỗi ngắn
magenest_merchant_entity_int          ← int, select, multiselect (lưu CSV)
magenest_merchant_entity_text         ← text dài
magenest_merchant_entity_datetime     ← date / datetime
magenest_merchant_entity_decimal      ← số thập phân
```

Mỗi bảng value có cùng cấu trúc:
```
value_id (PK) | attribute_id | store_id | entity_id | value
```
Cùng với UNIQUE KEY `(entity_id, attribute_id, store_id)` → đảm bảo mỗi attribute chỉ có 1 giá trị / 1 entity / 1 store.

**Quan hệ với core EAV tables:**
- `eav_entity_type` — đăng ký type `magenest_merchant`, trỏ về `entity_table = magenest_merchant_entity` và `entity_model = Magenest\Merchant\Model\ResourceModel\Merchant`.
- `eav_attribute` — mỗi attribute (store_name, merchant_status…) là 1 row. Column `backend_type` quyết định lưu vào bảng `_varchar` / `_int` / `_text` / `_datetime` / `_decimal`.
- `eav_attribute_set` + `eav_attribute_group` + `eav_entity_attribute` — gom các attribute thành "set" và "group" để render form.

### 3. Cách Magento tự động hiểu các bảng `_varchar`, `_int`...

**Quy ước đặt tên = phép ánh xạ ngầm.** `Magento\Eav\Model\Entity\AbstractEntity::getValueTablePrefix()` trả về `entity_table` của entity type (ở đây là `magenest_merchant_entity`). Khi save/load:

```php
// Pseudocode trong AbstractEntity
$valueTable = $this->getValueTablePrefix() . '_' . $attribute->getBackendType();
// → "magenest_merchant_entity" . "_" . "varchar" → "magenest_merchant_entity_varchar"
```

Vì vậy chỉ cần **đặt đúng tên bảng** theo pattern `<entity_table>_<backend_type>`, Magento tự lookup đúng. Đây là lý do tại sao trong `db_schema.xml` các bảng phải tên chính xác `magenest_merchant_entity_varchar`, `_int`, `_text`, `_datetime`, `_decimal`. Đổi tên → EAV không tìm thấy.

### 4. Đăng ký entity type qua data patch

`Setup/Patch/Data/CreateMerchantEntity.php`:
```php
$eavSetup->addEntityType('magenest_merchant', [
    'entity_model' => Magenest\Merchant\Model\ResourceModel\Merchant::class,
    'table'        => 'magenest_merchant_entity',
    ...
]);
```

`addEntityType()` thực hiện:
1. INSERT vào `eav_entity_type` 1 row mới.
2. Tự tạo 1 attribute set mặc định ("Default").
3. Tự tạo 1 attribute group ("General") thuộc set đó.

Sau patch này, có thể gọi `EavSetup::addAttribute('magenest_merchant', 'store_name', [...])` để thêm attribute như product/customer.

### 5. Resource model (`Model/ResourceModel/Merchant.php`)

Kế thừa `Magento\Eav\Model\Entity\AbstractEntity` và gọi `$this->setType('magenest_merchant')`. Đây là điểm "gắn" model vào entity type — từ đó Magento biết:
- Đọc / ghi vào `magenest_merchant_entity`
- Tải attribute từ `eav_attribute WHERE entity_type_id = X`
- Lưu giá trị vào `magenest_merchant_entity_<backend_type>`

Module override `_beforeSave()` để force-set `entity_type_id` và `attribute_set_id` từ DB nếu chưa có — đây là workaround cho cache issue của `EavConfig` khi entity type vừa được tạo trong cùng request (chi tiết ở phần [Lưu ý](#lưu-ý-quan-trọng--cạm-bẫy-gặp-phải)).

### 6. Tại sao chọn EAV thay vì flat table cho Merchant?

| Lý do | Giải thích |
|---|---|
| Mô hình thực tế khác nhau theo Merchant Type | Merchant "doanh nghiệp" có thể cần thêm "mã số thuế", merchant "cá nhân" cần "CCCD" — EAV cho phép thêm attribute mà không cần migration. |
| Tận dụng admin form/grid system của Magento | UI Component listing + form đọc trực tiếp từ `eav_attribute` → muốn hiện column mới chỉ cần INSERT thêm attribute. |
| Tích hợp với attribute set | Có thể tạo multiple attribute set ("Basic Merchant", "Premium Merchant") với các attribute khác nhau — đúng pattern catalog. |
| Multi-store value | Cột `store_id` trên mỗi value table cho phép một merchant có "store name" khác nhau theo store view (chưa dùng ở đây nhưng đã hỗ trợ sẵn). |
| Source / backend / frontend model | Khi cần custom (vd: chuyển VN→EN khi save, validate phone bằng regex), gắn `backend_model` / `frontend_model` mà không sửa CRUD code. |

**Đánh đổi:**
- Đọc 1 merchant đầy đủ = ~5-6 query JOIN (1 cho entity + 1 cho mỗi backend_type có attribute) thay vì 1 SELECT trên flat table.
- Phải tự viết UI DataProvider cho grid vì stock `Reporting::search()` chỉ SELECT từ entity table — không hydrate attribute values. Module giải quyết bằng `Ui/DataProvider/MerchantListing.php` (override `getData()` để gọi `getCollection()->getItems()` → trigger `_loadAttributes()`).

---

## Exam 1 — Quản lý Merchant

### Attribute set (15 attribute)

| Code | Type | Input | Source |
|---|---|---|---|
| `merchant_code` | varchar | text | — |
| `mcs_phone` | varchar | text | — |
| `store_name` | varchar | text | — |
| `category` | text | multiselect | `Model\Source\Category` |
| `active_date` | datetime | date | — |
| `latest_update_date` | datetime | date | — |
| `onboarding_date` | datetime | date | — |
| `merchant_status` | int | select | `Status` (Active/Pending/Blocked/Rejected) |
| `kyc_level` | int | select | — |
| `merchant_type` | int | select | — |
| `partner` | varchar | text | — |
| `dsas_phone` | varchar | text | — |
| `city` | int | select | `Model\Source\City` |
| `district` | int | select | `Model\Source\District` |
| `ward` | int | select | `Model\Source\Ward` |

### Admin URLs

| Route | Mô tả |
|---|---|
| `magenest_merchant/merchant/index` | Grid |
| `magenest_merchant/merchant/new` | Form tạo mới |
| `magenest_merchant/merchant/edit/entity_id/N` | Form edit |
| `magenest_merchant/merchant/save` | Save (POST) |
| `magenest_merchant/merchant/delete/entity_id/N` | Delete (GET với form_key) |
| `magenest_merchant/merchant/massDelete` | Mass delete |

Menu: **Merchants → Manage Merchants** (ACL: `Magenest_Merchant::merchant_manage`).

### Seed data

`AddSampleMerchants` chèn 10 row mẫu bằng **raw SQL** (bypass model layer) để né vấn đề cache của `EavConfig` khi entity type vừa tạo trong cùng `setup:upgrade` run. `RepairSampleMerchants` phụ cho trường hợp seed trước đó để lại rác.

---

## Exam 2 — Product attribute `merchant` + Elasticsearch search

### Cách Elasticsearch index store name

1. Product có attribute `merchant` (select, `int`), giá trị = `merchant.entity_id`.
2. Khi reindex `catalogsearch_fulltext`, `\Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::getAttributeOptionValue()` gọi `$attribute->getSource()->toOptionArray()` để build map `value → label`.
3. Source model `Magenest\Merchant\Model\Source\Product\MerchantOptions` trả label dạng `"Store Name (merchant_code)"`.
4. Indexer append label vào document ES của product → search "smile" sẽ trả product có merchant tên "Smile Store".

Flag attribute đặt: `searchable=true`, `filterable=true`, `filterable_in_search=true`, `search_weight=5`.

### Plugin `InvalidateCatalogSearchOnMerchantSave`

Khi merchant đổi `store_name`, các product gán merchant đó có document ES bị stale. Plugin gắn vào `MerchantRepositoryInterface::save()`:

- **`beforeSave`**: snapshot `store_name` hiện tại từ DB trực tiếp (không dùng `origData` vì EAV save đã overwrite trong cùng request).
- **`afterSave`**: nếu name thay đổi → query `catalog_product_entity_int WHERE attribute_id=<merchant_attr> AND value=<merchant_id>` lấy product IDs → `$indexerRegistry->get(Fulltext::INDEXER_ID)->reindexList($productIds)`.

**Đặc tính hiệu năng:** chỉ những product gán merchant đó mới reindex. Khớp với cách Magento core làm khi save product (`reindexRow` cho 1 ID, không invalidate toàn bộ index).

---

## Exam 3 — Customer attribute + Mass-assign 3k-5k

### Customer attribute `merchant_id`

`Setup/Patch/Data/AddMerchantCustomerAttribute.php`:
- `type=int`, `input=select`, source = `Model\Source\Customer\MerchantOptions`
- `used_in_forms = ['adminhtml_customer']` → hiển thị trên form edit customer
- `is_visible_in_grid = true` cho customer grid

### Massaction "Assign Merchant"

`view/adminhtml/ui_component/customer_listing.xml` extend stock listing, thêm action có URL trỏ về `magenest_merchant/customer_massassignmerchant/edit`.

### Flow

```
[Customer grid]
   ├─ tick N customers (hoặc Select All + filter)
   └─ Actions → Assign Merchant
        │
        ▼
[Edit controller]
   ├─ Filter::getCollection(customerCollectionFactory->create()) → resolve IDs
   ├─ Lưu IDs vào admin session
   └─ Render form picker (chọn merchant)
        │
        ▼
[Save controller — bulk SQL]
   ├─ Read IDs từ session, merchant_id từ POST
   ├─ Validate merchant tồn tại (1 fetchOne)
   ├─ Resolve attribute_id (EavConfig cache hit)
   ├─ beginTransaction()
   ├─ foreach array_chunk($ids, 1000):
   │      INSERT INTO customer_entity_int (attribute_id, entity_id, value)
   │      VALUES (...), (...), ...
   │      ON DUPLICATE KEY UPDATE value = VALUES(value)
   └─ commit()
        │
        ▼
[Redirect về customer grid + success message]
```

### Các quyết định tối ưu

| Quyết định | Lý do |
|---|---|
| **Bypass Customer model** | Load 5000 Customer model = 5000 EAV hydration + 5000 lần dispatch `customer_save_*` event + reindex per row → vài phút. SQL trực tiếp = vài trăm ms. |
| **`INSERT ... ON DUPLICATE KEY UPDATE`** | Unique key `(entity_id, attribute_id)` trên `customer_entity_int` → 1 query xử lý cả customer mới (INSERT) lẫn customer đã có merchant cũ (UPDATE). Không cần SELECT-then-write. |
| **Chunk 1000 rows** | Giữ memory bounded, an toàn với `max_allowed_packet` MySQL. 5k rows = 5 query thay vì 5000. |
| **Single transaction** | All-or-nothing — partial failure không để lại trạng thái nửa vời. |
| **Validate merchant qua fetchOne** | 1 query đơn giản, không hydrate EAV chỉ để check tồn tại. |
| **Selection qua session** | 5000 hidden inputs ≈ 30-50KB cho mỗi page. Session = 1 PHP array. |

### Estimate hiệu năng (5000 customers, MySQL 8, SSD local)

| Bước | Cost |
|---|---|
| Resolve IDs qua Filter | ~50-150ms |
| Validate merchant + attribute_id | ~5ms |
| 5 chunks × insertOnDuplicate(1000) | ~200-400ms |
| Commit | ~10ms |
| **Total** | **~300-600ms** |

So sánh nếu dùng `Customer` model save: 3-5 phút.

---

## Lưu ý quan trọng & cạm bẫy gặp phải

### 1. EAV cache khi entity type vừa được tạo trong cùng request

`EavConfig` cache "entity type không tồn tại" ngay lần đầu lookup. Nếu seed data patch tạo entity type rồi lập tức dùng `Magenest\Merchant\Model\Merchant` để save fixture → `getEntityTypeId()` trả `0` vì cache đã được populate trước khi entity tồn tại.

**Workaround:** `AddSampleMerchants` dùng **raw SQL** (`$connection->insert($table, $row)`) thay vì model layer cho seed data. `Model/ResourceModel/Merchant::_beforeSave()` cũng có fallback force-set `entity_type_id` từ DB nếu object chưa có.

### 2. Bảng `_varchar`/`_int`/... PHẢI đúng tên

Magento ánh xạ `<entity_table>_<backend_type>` ngầm. Đổi tên = hỏng EAV. Khi đổi `entity_table` trong `eav_entity_type`, phải đổi tên cả 5 bảng value tương ứng.

### 3. UI grid cho EAV entity cần custom DataProvider

Stock `Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider` chỉ SELECT từ entity table → cell attribute trả về empty. Module dùng `Ui/DataProvider/MerchantListing.php` override `getData()` để gọi `getCollection()->getItems()` (trigger `_loadAttributes()`).

### 4. `Magento\Backend\Block\Widget\Button\BackButton` không tồn tại

Stock Magento không có sẵn class này (khác với `SaveButton`/`DeleteButton`). Module tự tạo `Block/Adminhtml/Form/BackButton.php` extend `GenericButton`.

### 5. Controller `Delete` dùng GET, không phải POST

UI Component listing action column emit URL GET với `form_key` đính kèm. Vì vậy controller phải `implements HttpGetActionInterface`, không phải `HttpPostActionInterface`. Đây là khác biệt với CMS Page form (POST).

### 6. Mview catalogsearch không tự bắt thay đổi merchant

`catalogsearch_fulltext` mview chỉ watch `catalog_product_entity_*`, `cataloginventory_stock_item`, `catalog_category_product`... Module ghi vào `magenest_merchant_entity_varchar` → ngoài tầm theo dõi. Phải **manually** gọi `reindexList($productIds)` trong plugin của merchant repository.

### 7. `dataHasChangedFor()` không dùng được trong afterSave của EAV

`AbstractModel::afterSave()` gọi `setOrigData()` ngay lập tức → `dataHasChangedFor` luôn trả `false` trong afterSave plugin. Phải **snapshot trong beforeSave** rồi compare ở afterSave.

### 8. EAV admin form không tự set `entity_type_id` / `attribute_set_id` khi tạo mới

Form save qua admin POST không gửi 2 field này. `AbstractEntity::save()` có code tự set `entity_type_id` từ `getTypeId()` — nhưng nếu EavConfig stale thì ra `0`. `attribute_set_id` không có fallback nào. Module override `_beforeSave()` force-set cả 2 với DB fallback (`SELECT FROM eav_entity_type WHERE entity_type_code = ?`).

### 9. Module sequence

`module.xml` phải declare đầy đủ sequence:
```xml
<sequence>
    <module name="Magento_Eav"/>            <!-- entity type registration -->
    <module name="Magento_Backend"/>
    <module name="Magento_Ui"/>
    <module name="Magento_Catalog"/>        <!-- product attribute -->
    <module name="Magento_CatalogSearch"/>  <!-- fulltext indexer -->
    <module name="Magento_Indexer"/>
    <module name="Magento_Store"/>
    <module name="Magento_Customer"/>       <!-- customer attribute -->
</sequence>
```
Thiếu module → data patch sẽ chạy trước khi target entity tồn tại → silent fail hoặc lỗi cryptic.

### 10. Regenerate whitelist sau khi đổi `db_schema.xml`

```bash
php bin/magento setup:db-declaration:generate-whitelist --module-name=Magenest_Merchant
```
Không có whitelist → declarative schema sẽ không drop column/table cũ.
