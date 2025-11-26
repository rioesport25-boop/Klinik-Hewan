# 🎉 Integrasi Midtrans Payment Gateway - Klinik Hewan Petshop

## 📋 Ringkasan Integrasi

Integrasi Midtrans Payment Gateway telah **BERHASIL** diimplementasikan pada sistem petshop Klinik Hewan. Semua komponen dari Tripay telah diganti dengan Midtrans.

---

## ✅ Komponen yang Telah Diimplementasikan

### 1. **Backend (Laravel)**

#### a. **Package & Dependencies**

-   ✅ Midtrans PHP SDK v2.6.2 terinstall
-   ✅ Konfigurasi Midtrans (`config/midtrans.php`)
-   ✅ Environment variables di `.env`

#### b. **Database**

-   ✅ Migration untuk update tabel `payments`:
    -   Menghapus kolom Tripay (tripay_reference, tripay_merchant_ref, dll)
    -   Menambah kolom Midtrans (midtrans_order_id, snap_token, va_number, dll)

#### c. **Models**

-   ✅ `Payment` model updated dengan:
    -   Fillable fields untuk Midtrans
    -   Methods: `isQris()`, `isVirtualAccount()`, `isEwallet()`, `isCreditCard()`, `isConvenienceStore()`
    -   Transaction status checkers: `isTransactionPending()`, `isTransactionSuccess()`, dll

#### d. **Services**

-   ✅ `MidtransService` baru dengan fungsi:
    -   `createTransaction()` - Membuat transaksi Snap
    -   `handleNotification()` - Handle webhook dari Midtrans
    -   `checkTransactionStatus()` - Cek status transaksi
    -   `cancelTransaction()` - Batalkan transaksi

#### e. **Controllers**

-   ✅ `CheckoutController` updated:

    -   Full checkout flow dengan Midtrans
    -   Create order & order items
    -   Stock management
    -   Integration dengan MidtransService
    -   Return snap_token ke frontend

-   ✅ `MidtransController` baru:
    -   `notification()` - Webhook handler
    -   `finish()` - Redirect setelah pembayaran selesai
    -   `unfinish()` - Redirect jika pembayaran belum selesai
    -   `error()` - Redirect jika ada error
    -   `paymentStatus()` - Tampilkan status pembayaran

#### f. **Routes**

-   ✅ Web routes untuk payment callbacks
-   ✅ API route untuk webhook notification: `/api/midtrans/notification`

### 2. **Frontend (Vue.js + Inertia)**

#### a. **Checkout Page** (`Petshop/Checkout/Index.vue`)

-   ✅ Load Midtrans Snap script otomatis
-   ✅ Watch untuk snap_token dari flash messages
-   ✅ Auto trigger Snap popup setelah checkout berhasil
-   ✅ Callback handlers (onSuccess, onPending, onError, onClose)

#### b. **Payment Status Page** (`Petshop/Payment/Status.vue`)

-   ✅ Tampilan status pembayaran (pending, success, failed, dll)
-   ✅ Info nomor pesanan dan total
-   ✅ VA Number display (jika tersedia)
-   ✅ Link untuk lanjutkan pembayaran (jika pending)
-   ✅ Action buttons (Lanjut Belanja, Lihat Pesanan)

---

## 🔧 Konfigurasi Midtrans

### **Kredensial Sandbox (Development)**

```env
MIDTRANS_MERCHANT_ID=your-merchant-id
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_IS_PRODUCTION=false
```

> **⚠️ PENTING:** Ganti kredensial di atas dengan kredensial Midtrans Sandbox Anda sendiri.
> Dapatkan dari [Midtrans Dashboard](https://dashboard.midtrans.com/) > Settings > Access Keys

### **URL Endpoints untuk Midtrans Dashboard**

Anda perlu set URL berikut di **Midtrans Dashboard > Settings > Configuration**:

1. **Payment Notification URL (Webhook):**

    ```
    http://yourwebsite.com/api/midtrans/notification
    ```

2. **Finish Redirect URL:**

    ```
    http://yourwebsite.com/petshop/payment/finish
    ```

3. **Unfinish Redirect URL:**

    ```
    http://yourwebsite.com/petshop/payment/unfinish
    ```

4. **Error Redirect URL:**
    ```
    http://yourwebsite.com/petshop/payment/error
    ```

> **Note:** Ganti `http://yourwebsite.com` dengan domain actual Anda. Untuk testing lokal, gunakan tools seperti **ngrok** atau **localtunnel** agar Midtrans bisa akses localhost Anda.

---

## 🚀 Cara Testing

### **1. Persiapan**

```bash
# Pastikan server berjalan
php artisan serve

# Pastikan database migration sudah dijalankan
php artisan migrate

# Build assets frontend
npm run build
```

### **2. Flow Testing**

1. **Buka halaman Petshop:**

    - Navigate to: `http://127.0.0.1:8000/petshop`

2. **Tambah produk ke cart:**

    - Pilih produk
    - Klik "Add to Cart"

3. **Checkout:**

    - Go to Cart
    - Klik "Proceed to Checkout"
    - Isi form checkout
    - Pilih shipping method
    - Pilih payment channel (semua channel akan ada di Snap popup)
    - Submit

4. **Pembayaran:**
    - Snap popup Midtrans akan muncul otomatis
    - Pilih metode pembayaran (Credit Card, VA, GoPay, QRIS, dll)
    - Untuk **testing**, gunakan kredensial test dari Midtrans:

### **3. Test Cards (Sandbox)**

**Credit Card Test Numbers:**

```
Card Number: 4811 1111 1111 1114
CVV: 123
Exp: 01/25
OTP: 112233

// Untuk simulasi different scenarios:
- Success: 4811 1111 1111 1114
- Deny: 4911 1111 1111 1113
- Challenge: 4411 1111 1111 1118
```

**Test VA Numbers:**

-   BCA, BNI, BRI: Akan generate nomor VA otomatis
-   Bayar melalui simulator di dashboard Midtrans

**Test E-wallet:**

-   GoPay: Akan generate QR code
-   ShopeePay: Deep link ke app

---

## 📦 Metode Pembayaran yang Tersedia

✅ Credit Card (Visa, Mastercard, JCB, Amex)  
✅ BCA Virtual Account  
✅ BNI Virtual Account  
✅ BRI Virtual Account  
✅ Permata Virtual Account  
✅ Other Bank VA  
✅ GoPay  
✅ ShopeePay  
✅ QRIS  
✅ Alfamart  
✅ Indomaret

---

## 🔄 Webhook Notification Flow

1. Customer melakukan pembayaran di Midtrans
2. Midtrans send HTTP POST ke `/api/midtrans/notification`
3. `MidtransController@notification` menerima data
4. `MidtransService@handleNotification` process data:
    - Update payment record
    - Update transaction_status
    - Mark as paid jika settlement/capture
    - Mark as failed jika deny/cancel/expire
5. Update order status accordingly

---

## 🎯 Status Pembayaran

| Midtrans Status | Deskripsi                | Action                       |
| --------------- | ------------------------ | ---------------------------- |
| `pending`       | Menunggu pembayaran      | Show VA/payment instructions |
| `capture`       | Pembayaran berhasil (CC) | Mark as paid                 |
| `settlement`    | Pembayaran berhasil      | Mark as paid                 |
| `deny`          | Ditolak                  | Mark as failed               |
| `cancel`        | Dibatalkan               | Mark as failed               |
| `expire`        | Kadaluarsa               | Mark as failed               |

---

## 🛠️ Troubleshooting

### **Problem: Snap popup tidak muncul**

**Solution:**

-   Check browser console untuk error
-   Pastikan `VITE_MIDTRANS_CLIENT_KEY` sudah di `.env`
-   Cek apakah Snap script berhasil diload

### **Problem: Webhook tidak jalan**

**Solution:**

-   Pastikan URL notification sudah diset di Midtrans Dashboard
-   Untuk local testing, gunakan **ngrok**: `ngrok http 8000`
-   Check Laravel logs: `storage/logs/laravel.log`

### **Problem: Order created tapi payment failed**

**Solution:**

-   Check exception di catch block
-   Database transaction akan rollback otomatis
-   Lihat error message di flash message

---

## 📝 Migration ke Production

Ketika siap production:

1. **Update `.env`:**

    ```env
    MIDTRANS_IS_PRODUCTION=true
    MIDTRANS_MERCHANT_ID=[your_production_merchant_id]
    MIDTRANS_CLIENT_KEY=[your_production_client_key]
    MIDTRANS_SERVER_KEY=[your_production_server_key]
    ```

2. **Update Midtrans Dashboard:**

    - Switch ke Production environment
    - Set URL callbacks dengan domain production
    - Activate payment methods yang diinginkan

3. **Test thoroughly:**
    - Test semua payment methods
    - Verify webhook berjalan
    - Check order flow end-to-end

---

## 📞 Support

Jika ada pertanyaan atau issue:

1. Check Midtrans Documentation: https://docs.midtrans.com
2. Check Laravel logs: `storage/logs/laravel.log`
3. Check browser console untuk frontend issues
4. Midtrans Support: support@midtrans.com

---

## ✨ Fitur Tambahan (Optional Future Enhancement)

-   [ ] Recurring payment untuk subscription
-   [ ] Installment payment (cicilan)
-   [ ] Promo code integration
-   [ ] Send email notification setelah pembayaran
-   [ ] SMS notification via Midtrans
-   [ ] Refund functionality
-   [ ] Admin panel untuk monitor payments

---

**🎊 INTEGRASI MIDTRANS COMPLETE!**

Sistem petshop Klinik Hewan sekarang sudah siap menerima pembayaran melalui Midtrans dengan berbagai metode pembayaran. Happy testing! 🚀
