# SweetAlert2 Implementation Guide

## Installation
SweetAlert2 telah diinstal dan dikonfigurasi di project ini.

## Cara Penggunaan

### Import di Component Vue
```javascript
import { showSuccess, showError, showWarning, showInfo, showConfirm, showLoading, closeSwal } from '@/Plugins/sweetalert';
```

### 1. Success Notification
```javascript
showSuccess('Data berhasil disimpan!');
// atau dengan custom title
showSuccess('Data berhasil disimpan!', 'Berhasil!');
```

### 2. Error Notification
```javascript
showError('Gagal menyimpan data');
// atau dengan custom title
showError('Gagal menyimpan data', 'Terjadi Kesalahan!');
```

### 3. Warning Notification
```javascript
showWarning('Stok produk hampir habis');
// atau dengan custom title
showWarning('Stok produk hampir habis', 'Peringatan!');
```

### 4. Info Notification
```javascript
showInfo('Fitur ini masih dalam pengembangan');
// atau dengan custom title
showInfo('Fitur ini masih dalam pengembangan', 'Informasi');
```

### 5. Confirmation Dialog
```javascript
showConfirm({
    title: 'Apakah Anda yakin?',
    text: 'Tindakan ini tidak dapat dibatalkan!',
    icon: 'warning',
    confirmButtonText: 'Ya, lanjutkan!',
    cancelButtonText: 'Batal'
}).then((result) => {
    if (result.isConfirmed) {
        // User clicked confirm
        // Do something...
    }
});
```

### 6. Loading Dialog
```javascript
// Show loading
showLoading('Memproses data...');

// Do async operation
await someAsyncFunction();

// Close loading
closeSwal();
```

### 7. Global Access (via $swal)
Di dalam component, Anda juga bisa menggunakan:
```javascript
this.$swal.showSuccess('Berhasil!');
this.$swal.showError('Gagal!');
// dll...
```

## Contoh Implementasi

### Form Submit dengan Loading
```javascript
const handleSubmit = async () => {
    showLoading('Menyimpan data...');
    
    try {
        await axios.post('/api/save', formData);
        closeSwal();
        showSuccess('Data berhasil disimpan!');
    } catch (error) {
        closeSwal();
        showError('Gagal menyimpan data');
    }
};
```

### Delete dengan Konfirmasi
```javascript
const deleteItem = (id) => {
    showConfirm({
        title: 'Hapus Item?',
        text: 'Data yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/items/${id}`, {
                onSuccess: () => {
                    showSuccess('Item berhasil dihapus!');
                }
            });
        }
    });
};
```

### Inertia Form dengan Error Handling
```javascript
form.post('/booking', {
    onSuccess: () => {
        showSuccess('Booking berhasil dibuat!');
    },
    onError: (errors) => {
        const firstError = Object.values(errors)[0];
        showError(firstError || 'Terjadi kesalahan!');
    }
});
```

## Styling
SweetAlert2 menggunakan default styling yang sudah bagus. Jika perlu customize:

```javascript
import { Swal } from '@/Plugins/sweetalert';

Swal.fire({
    title: 'Custom Alert',
    html: '<b>Bold text</b><br>Custom HTML content',
    icon: 'success',
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    // ... custom options
});
```

## Available Icons
- `success` - ✓ Green checkmark
- `error` - ✗ Red cross
- `warning` - ⚠ Yellow warning
- `info` - ℹ Blue info
- `question` - ? Question mark

## Toast Position
Default position adalah `top-end`. Untuk mengubah:
```javascript
Toast.fire({
    position: 'top-start', // top-start, top, top-end, center-start, center, center-end, bottom-start, bottom, bottom-end
    icon: 'success',
    title: 'Signed in successfully'
});
```
