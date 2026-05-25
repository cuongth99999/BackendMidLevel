# Magenest_ConfigurableChildList

Module tuỳ biến lại Product Details Page (PDP) cho **Configurable Product**: thay vì hiển thị attribute cuối cùng dưới dạng `<select>` / swatch, module render danh sách **child products** khớp với các attribute đã chọn, mỗi card có name, SKU, giá, ô nhập qty và nút Add to Cart riêng.

## Bối cảnh

Mặc định Magento render mọi super-attribute (Color, Size, Voltage, …) của configurable product thành dropdown hoặc swatch. Khách hàng muốn:

- Giữ nguyên các attribute đầu (dropdown / swatch như cũ).
- Thay attribute **cuối cùng** (theo `position` admin cấu hình) bằng danh sách các child product khớp với combo attribute đã chọn.
- Mỗi child có nút Add to Cart + ô qty riêng, không reload trang.
- Giữ nguyên style Magento Luma mặc định.

## Tính năng

- Tự động phát hiện attribute cuối (theo position) và ẩn `.field.configurable` / `.swatch-attribute` tương ứng.
- Ẩn luôn `.box-tocart` của parent (vì mỗi child đã có nút riêng).
- Lọc động: mỗi lần đổi giá trị bất kỳ attribute nào, danh sách child re-render client-side, không gọi server.
- Mỗi card hiển thị: ảnh (preset `product_small_image` 135×135 của Luma), name, SKU, short description (nếu có), giá đã format theo currency hiện tại, qty (mặc định 1), nút Add to Cart.
- Child out-of-stock vẫn hiển thị nhưng nút Add to Cart bị disabled với label "Out of Stock".
- Add to Cart: POST AJAX tới `/checkout/cart/add` với full `super_attribute[*]` payload của child được chọn, sau đó `customerData.reload(['cart'], false)` để minicart update tức thì.
- Tương thích cả **dropdown** (`<select>`) lẫn **swatch** (`<input>` ẩn do `Magento_Swatches` render).
- Hỗ trợ i18n đầy đủ cho mọi chuỗi UI.

## Yêu cầu

- PHP 8.1+
- Magento 2.4.x
- `Magento_ConfigurableProduct`, `Magento_Checkout`, `Magento_Customer`, `Magento_Catalog`

## Cài đặt

1. Copy module vào `app/code/Magenest/ConfigurableChildList`.
2. Chạy:

   ```bash
   php bin/magento module:enable Magenest_ConfigurableChildList
   php bin/magento setup:upgrade
   php bin/magento setup:di:compile
   php bin/magento cache:flush
   # Production mode:
   php bin/magento setup:static-content:deploy -f
   ```

## Cấu trúc module

```
ConfigurableChildList/
├── Block/
│   └── Product/
│       └── View/
│           └── Type/
│               └── ChildList.php          # Extend Configurable block, build JSON payload
├── etc/
│   └── module.xml
├── view/
│   └── frontend/
│       ├── layout/
│       │   └── catalog_product_view_type_configurable.xml
│       ├── templates/
│       │   └── product/view/type/
│       │       └── child_list.phtml       # Wrapper + data-mage-init
│       └── web/
│           ├── css/
│           │   └── child-list.less        # BEM, blend với Luma defaults
│           └── js/
│               └── child-list.js          # jQuery widget magenest.configurableChildList
├── composer.json
├── registration.php
└── README.md
```

## Kiến trúc

### Block — `Block/Product/View/Type/ChildList.php`

Extend `Magento\ConfigurableProduct\Block\Product\View\Type\Configurable` để tái sử dụng:

- `getAllowAttributes()` — trả về configurable attributes đã sort theo position.
- `getAllowProducts()` — trả về child products đã enabled.
- `priceCurrency`, `jsonEncoder` — đã được inject sẵn.

Block thêm:

- `shouldRender(): bool` — chỉ render khi product là configurable và có attrs + children.
- `getChildListConfig(): string` — encode JSON payload cho widget JS, gồm:
  - `productId`, `addToCartUrl` (`Checkout\Helper\Cart::getAddUrl`), `formKey`.
  - `attributes` (map `attrId => {id, code, label}`) và `orderedAttrIds` (sort theo position).
  - `lastAttributeId` — phần tử cuối của `orderedAttrIds`.
  - `children` — mỗi item: `{id, sku, name, short_description, image, price, price_raw, attributes:{attrId:valueId}, in_stock}`.
  - `i18n` — chuỗi translate cho mọi label UI.

Image dùng preset `product_small_image` (135×135 — Luma `view.xml`). Price lấy từ `getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)` rồi format qua `PriceCurrencyInterface::format()`.

### Layout — `view/frontend/layout/catalog_product_view_type_configurable.xml`

Thêm block vào `product.info.options.wrapper` ngay sau `product.info.options.configurable` (đã có sẵn từ `Magento_ConfigurableProduct`). Khai báo `<css src="Magenest_ConfigurableChildList::css/child-list.css"/>` ở `<head>` để Magento tự compile từ `.less`.

### Template — `view/frontend/templates/product/view/type/child_list.phtml`

Render wrapper div với `data-mage-init` chứa JSON config. Hai slot rỗng `[data-role="items"]` và `[data-role="empty"]` cho widget render vào.

### Widget JS — `view/frontend/web/js/child-list.js`

jQuery widget `magenest.configurableChildList`:

1. `_create` — gọi 4 helper:
   - `_hideLastAttribute()` ẩn `.field.configurable` (dropdown) hoặc `.swatch-attribute[data-attribute-id]` (swatch). Re-apply sau 200ms và 800ms vì swatches có thể init sau widget này.
   - `_hideMainAddToCart()` ẩn `.box-tocart` của parent.
   - `_bindEvents()` lắng nghe `change.magenestChildList` trên `.super-attribute-select` (cả `<select>` lẫn `<input>` đều có class này) + click `[data-role=add-to-cart]`.
   - `_refresh()` lần đầu (sẽ hiện empty message).

2. `_getSelectedNonLast()` — đọc value của mọi `.super-attribute-select` trừ attribute cuối, parse `super_attribute[<id>]` từ `name` để biết attribute id.

3. `_refresh()` — yêu cầu **mọi** non-last attribute đều có value (nếu không hiển thị empty message); sau đó `_.filter(children, ...)` so khớp toàn bộ giá trị attribute để chọn child phù hợp.

4. `_buildCard(item)` — render DOM card bằng jQuery (KHÔNG dùng string concat HTML để tránh XSS), giá dùng `.text()` vì `priceCurrency->format(..., false)` trả về plain text.

5. `_handleAdd($btn)` — build `FormData` với `form_key`, `product=parentId`, `qty`, `selected_configurable_option=childId`, full `super_attribute[*]` của child; POST tới `addToCartUrl`. Trên success → `customerData.reload(['cart'], false)` + inline success notice. Trên fail → `Magento_Ui/js/modal/alert`. Body có loader `processStart`/`processStop`.

### LESS — `view/frontend/web/css/child-list.less`

BEM (`.magenest-child-list`, `.magenest-child-card`). Mobile-first: stack column dưới 640px. Dùng colors khớp Luma (#1979c3 cho accent, #cccccc cho border).

## Security

- Form key trên mọi POST.
- `selected_configurable_option` và `super_attribute[*]` được Magento `Configurable::prepareForCartAdvanced()` validate server-side; client gửi sai cũng không add nhầm.
- Mọi text trong JS render bằng `text` option của jQuery (không phải `html`), tránh XSS từ tên / SKU / description.
- JSON config được `Magento\Framework\Json\EncoderInterface::encode()` escape.
- Block extends `Configurable` nên `getCacheKeyInfo()` đã bao gồm `priceCurrency` và `customerGroupId`, không leak cache giữa các customer group.

## Verification

1. Mở PDP của một configurable product (ví dụ V6 HotEnd 4 attributes).
2. Confirm attribute cuối (`V6-Extras` chẳng hạn) không còn hiển thị dropdown / swatch.
3. Confirm `.box-tocart` chính bị ẩn.
4. Chưa chọn đủ attribute → empty message "Please choose the options above...".
5. Chọn đủ các attribute khác → list child cards xuất hiện, mỗi child có name/SKU/price/qty/Add to Cart.
6. Đổi giá trị 1 attribute → list re-render tức thì.
7. Click Add to Cart với qty=2 → minicart counter tăng đúng, vào `/checkout/cart/` confirm đúng child variant với qty=2.
8. Child out-of-stock → nút disabled với label "Out of Stock".
9. Console không có JS error; Network tab chỉ có 1 POST tới `/checkout/cart/add/...`.

## Tương thích

- **Dropdown** (template native `configurable.phtml`): hoạt động.
- **Swatch** (`Magento_Swatches`): hoạt động — widget detect cả `<select>` và `<input class="super-attribute-select">`.
- **Single attribute configurable**: hoạt động — `nonLastIds = []`, mọi children hiển thị ngay từ đầu.
- **FPC**: an toàn vì kế thừa cache key từ block cha.

## Branch

`Section7_Exam1` — Exam 1 / Section 7: Custom Product Options.
