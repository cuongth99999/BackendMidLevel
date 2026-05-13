# Magento 2 AJAX Cart Module

Module này cải thiện trải nghiệm giỏ hàng bằng cách thêm chức năng AJAX cho các thao tác cập nhật giỏ hàng.

## Tính năng

- Cập nhật số lượng sản phẩm trong giỏ hàng không cần tải lại trang
- Xóa sản phẩm khỏi giỏ hàng qua AJAX
- Cập nhật tùy chọn sản phẩm trong giỏ hàng
- Hiển thị loading animation trong quá trình xử lý
- Hiển thị thông báo thành công/lỗi
- Cập nhật tự động các thông tin tổng giỏ hàng
- Xử lý các edge cases và lỗi

## Yêu cầu

- PHP ~7.4.0||~8.1.0
- Magento 2.4.x

## Cài đặt

1. Copy module vào thư mục `app/code/Magenest/AjaxCart`
2. Chạy các lệnh sau:

```bash
php bin/magento module:enable Magenest_AjaxCart
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:clean
```

## Kiến trúc

Module được tổ chức theo cấu trúc sau:

```
AjaxCart/
├── Block/
│   └── Cart/
│       └── Grid.php
├── Controller/
│   └── Cart/
│       ├── Delete.php
│       ├── UpdateItemOptions.php
│       └── UpdatePost.php
├── Test/
│   └── Unit/
│       └── Controller/
│           └── Cart/
│               ├── DeleteTest.php
│               └── UpdatePostTest.php
├── view/
│   └── frontend/
│       ├── layout/
│       │   └── checkout_cart_index.xml
│       ├── templates/
│       │   ├── cart/
│       │   │   └── form.phtml
│       │   └── loader.phtml
│       └── web/
│           ├── css/
│           │   └── source/
│           │       └── _module.less
│           └── js/
│               ├── action/
│               │   └── get-totals-mixin.js
│               └── cart.js
├── composer.json
├── etc/
│   ├── frontend/
│   │   └── routes.xml
│   └── module.xml
├── registration.php
└── README.md
```

## Testing

Module bao gồm các unit test cho các controller chính. Để chạy test:

```bash
php vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Magenest/AjaxCart/Test/Unit