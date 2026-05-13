# Magento 2 Booking Schedule Module

Module cho phép admin nhập và quản lý **số lượng booking tối đa (stock)** theo từng khung giờ 30 phút, theo từng ngày trong tuần. Giao diện grid được xây dựng bằng **KnockoutJS** (Magento UI Component) với điều hướng tuần, cuộn khung giờ, copy tuần và lưu bằng AJAX.

## Tính năng

- Grid 7 ngày × 48 slot (mỗi 30 phút từ 00:00 đến 23:30)
- Mỗi ô hiển thị: `reservation`, `used`, và ô nhập `Stock` (sức chứa booking tối đa)
- Ô có `stock > 0` được tô **xanh** để dễ nhận biết slot đang mở
- Nút **◄ ►** chuyển sang tuần trước / tuần sau (snap về thứ Hai của tuần đó)
- Nút **▲ ▼** cuộn lên / xuống để xem các khung giờ khác trong ngày
- Nút **Copy Week Assignment** copy dữ liệu stock của tuần hiện tại sang `X` tuần kế tiếp (X do admin nhập, tối đa 52)
- Nút **Save** lưu thay đổi qua AJAX, **chỉ gửi các ô đã chỉnh sửa** (dirty-tracking) để tối ưu request
- Loading indicator (`processStart/processStop`) và alert thành công / lỗi
- ACL riêng (`Magenest_BookingSchedule::schedule`) — admin nào có quyền mới truy cập được
- Endpoint bảo vệ bằng URL secret-key của admin + `CsrfAwareActionInterface`
- Đầu vào được validate bằng regex (date `YYYY-MM-DD`, time `HH:00` / `HH:30`) trước khi ghi DB

## Yêu cầu

- PHP ~8.1.0 || ~8.2.0 || ~8.3.0
- Magento 2.4.x

## Cài đặt

1. Copy module vào thư mục `app/code/Magenest/BookingSchedule`
2. Chạy các lệnh sau (theo đúng thứ tự — whitelist phải sinh **trước** `setup:upgrade`):

```bash
php bin/magento module:enable Magenest_BookingSchedule
php bin/magento setup:db-declaration:generate-whitelist --module-name=Magenest_BookingSchedule
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f -a adminhtml
php bin/magento cache:clean
```

## Cách sử dụng

1. Vào trang admin → menu **Content → Booking Schedule**.
2. Mặc định grid hiển thị tuần hiện tại, từ slot 06:30.
3. **Nhập stock** vào ô `Stock` của bất kỳ slot nào — ô sẽ tự đổi sang xanh nếu giá trị > 0.
4. **Chuyển tuần**: bấm ◄ hoặc ► ở hai góc trên của bảng.
5. **Cuộn khung giờ**: bấm ▲ ở đầu bảng hoặc ▼ ở cuối bảng để xem các slot trước / sau.
6. **Copy tuần**: nhập số tuần cần copy vào ô bên cạnh nút **Copy Week Assignment** rồi bấm nút. Dữ liệu `stock` của tuần hiện tại sẽ được copy sang `X` tuần kế tiếp (các ô đã có sẵn ở tuần đích sẽ bị **ghi đè** stock; `used`/`reservation` không bị thay đổi).
7. **Lưu**: bấm nút **Save** (cam, góc dưới phải). Chỉ những ô đã được edit trong session hiện tại mới được gửi đi.

## Kiến trúc

```
BookingSchedule/
├── Api/
│   ├── BookingScheduleRepositoryInterface.php
│   └── Data/
│       └── BookingScheduleInterface.php
├── Block/
│   └── Adminhtml/
│       └── Schedule.php
├── Controller/
│   └── Adminhtml/
│       └── Schedule/
│           ├── Index.php       # GET trang admin
│           ├── GetWeek.php     # AJAX GET dữ liệu tuần
│           ├── Save.php        # AJAX POST upsert các ô dirty
│           └── CopyWeek.php    # AJAX POST copy tuần
├── Model/
│   ├── BookingSchedule.php
│   ├── BookingScheduleRepository.php
│   └── ResourceModel/
│       ├── BookingSchedule.php
│       └── BookingSchedule/
│           └── Collection.php
├── etc/
│   ├── module.xml
│   ├── di.xml
│   ├── acl.xml
│   ├── db_schema.xml
│   ├── db_schema_whitelist.json
│   └── adminhtml/
│       ├── routes.xml
│       └── menu.xml
├── view/
│   └── adminhtml/
│       ├── layout/
│       │   └── magenest_bookingschedule_schedule_index.xml
│       ├── templates/
│       │   └── schedule.phtml          # mount KO ui-component
│       └── web/
│           ├── js/view/schedule.js     # KO ViewModel
│           ├── template/schedule.html  # KO template
│           └── css/schedule.css
├── composer.json
├── registration.php
└── README.md
```

## Thiết kế Database

Bảng `magenest_booking_schedule` được thiết kế để **tối ưu cho các query tìm kiếm theo ngày / slot available**:

| Cột             | Kiểu              | Ghi chú                                |
|-----------------|-------------------|----------------------------------------|
| `entity_id`     | INT UNSIGNED PK   | Auto-increment                         |
| `schedule_date` | DATE              | Ngày của slot (YYYY-MM-DD)             |
| `schedule_time` | VARCHAR(5)        | Giờ của slot (HH:MM, bước 30 phút)     |
| `stock`         | SMALLINT UNSIGNED | Sức chứa booking tối đa                |
| `used`          | SMALLINT UNSIGNED | Số booking đã dùng                     |
| `reservation`   | SMALLINT UNSIGNED | Số booking đang giữ chỗ                |
| `created_at`    | TIMESTAMP         | Auto                                   |
| `updated_at`    | TIMESTAMP         | Auto                                   |

**Index / Constraint:**

- `UNIQUE (schedule_date, schedule_time)` — mỗi cặp (ngày, giờ) chỉ tồn tại 1 record → upsert đơn giản
- `INDEX (schedule_date)` — range-scan nhanh khi load 7 ngày
- `INDEX (stock)` — lọc nhanh các slot có stock > 0

### Ví dụ query: "Ngày có > 3 slot giờ available"

```sql
SELECT schedule_date, COUNT(*) AS available_slots
FROM magenest_booking_schedule
WHERE schedule_date BETWEEN :from AND :to
  AND (stock - used - reservation) > 0
GROUP BY schedule_date
HAVING available_slots > 3;
```

## Endpoints AJAX

| Method | URL                                                | Mục đích                                          |
|--------|----------------------------------------------------|--------------------------------------------------|
| GET    | `/admin/magenest_bookingschedule/schedule/getWeek` | Lấy dữ liệu 7 ngày từ Monday `week_start`        |
| POST   | `/admin/magenest_bookingschedule/schedule/save`    | Upsert mảng `slots: [{date, time, stock}, ...]`  |
| POST   | `/admin/magenest_bookingschedule/schedule/copyWeek`| Copy tuần `week_start` sang `copies` tuần kế tiếp|

Tất cả endpoint:

- Bảo vệ bởi ACL `Magenest_BookingSchedule::schedule`
- URL admin chứa secret-key (chống CSRF cấp Magento backend)
- Validate input phía server (regex date / time, clamp số âm về 0, cap `copies` ≤ 52)
- Trả JSON `{success: bool, message?: string, ...}`

## Lưu ý

- Module chỉ ghi cột `stock` từ form admin. Cột `used` và `reservation` được dành cho các flow booking thực tế (đặt chỗ / xác nhận) — sẽ được update bởi service khác khi tích hợp về sau.
- Khi copy tuần, các ô đã tồn tại ở tuần đích sẽ bị **ghi đè stock**. Nếu cần policy khác (skip khi tồn tại / cộng dồn), sửa trong `Controller/Adminhtml/Schedule/CopyWeek.php`.
- Giới hạn an toàn: tối đa 52 tuần / lần copy để tránh chạy quá lâu trên admin request.
