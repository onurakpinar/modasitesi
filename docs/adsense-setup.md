# AdSense Kurulum Rehberi

Bu rehber, ModaPusula sitesinde AdSense uyum altyapısının nasıl kullanılacağını açıklar.

**Önemli:** AdSense onayı garanti edilmez. Bu modül yalnızca Google program politikalarına uygun teknik hazırlık sağlar.

## 1. Google AdSense Hesabına Site Ekleme

1. [Google AdSense](https://www.google.com/adsense/) hesabınıza giriş yapın.
2. **Siteler** bölümünden sitenizi ekleyin.
3. Google’ın verdiği `ca-pub-XXXXXXXXXXXXXXXX` client kimliğini not edin.

## 2. Doğrulama Client ID Ekleme

Doğrulama scripti ile gerçek reklam gösterimi **ayrıdır**.

1. Admin panelinde **AdSense ve Gizlilik** bölümüne gidin.
2. **AdSense doğrulama scriptini head alanına ekle** seçeneğini işaretleyin.
3. Geçerli `ca-pub-` client ID girin.
4. Kaydedin ve üretim ortamında sitenin kaynak kodunda scriptin bir kez yüklendiğini doğrulayın.

Yerel (`local`) ve test (`testing`) ortamlarında gerçek AdSense scripti **yüklenmez**; yalnızca güvenli placeholder yorum görünür.

## 3. Publisher ID ve ads.txt

1. AdSense hesabınızdaki `pub-XXXXXXXXXXXXXXXX` kimliğini admin paneline girin.
2. `/ads.txt` adresini kontrol edin. Geçerli publisher ID varsa şu satır üretilir:

```
google.com, pub-XXXXXXXXXXXXXXXX, DIRECT, f08c47fec0942fa0
```

Publisher ID yoksa sahte kimlik **üretilmez**; açıklayıcı yorum döner.

## 4. Sertifikalı CMP Kurulumu (Manuel)

Bu proje basit bir çerez banner’ı sunmaz ve bunu “CMP uyumlu” olarak tanıtmaz.

**Avrupa Ekonomik Alanı (AEA), Birleşik Krallık ve İsviçre** ziyaretçileri için Google AdSense kullanırken [Google sertifikalı bir CMP](https://support.google.com/adsense/answer/13554137) zorunludur.

Önerilen yollar:

1. **Google AdSense Privacy & Messaging** — AdSense hesabınızdan Privacy & Messaging bölümünü açın, mesajları yapılandırın ve siteye Google’ın sağladığı entegrasyonu tamamlayın.
2. **Google sertifikalı üçüncü taraf CMP** — IAB TCF uyumlu, Google listesinde yer alan bir sağlayıcı seçin; kurulum talimatlarını sağlayıcının panelinden uygulayın.

CMP kurulumunu tamamladıktan sonra admin panelinde **Google sertifikalı CMP yapılandırıldı** kutusunu işaretleyin. Bu onay, reklam kutularının açılması için zorunludur.

Analytics veya pazarlama scriptleri varsayılan olarak eklenmez.

## 5. Sabit Sayfaları Tamamlama

Admin panelinden **Sabit Sayfalar** bölümünde şu sayfaları doldurup yayınlayın:

- Hakkımızda
- İletişim
- Gizlilik Politikası
- Çerez Politikası
- Kullanım Koşulları
- Yayın İlkeleri
- Düzeltme Politikası

`[ISLETME_UNVANI]`, `[ILETISIM_EPOSTA]` gibi köşeli parantezli alanları gerçek bilgilerle değiştirin. Bu alanlar doldurulmadan sayfa ziyaretçiye açılmaz.

## 6. Başvuru Öncesi Kontrol Listesi

Admin panelindeki **AdSense Başvuru Hazırlık Kontrolü** listesini kullanın. Öne çıkan maddeler:

- Gerçek site adı ve iletişim e-postası
- Tüm zorunlu sabit sayfalar yayında ve eksiksiz
- En az 20 özgün, insan kontrolünden geçmiş yazı *(proje içi kalite eşiği; Google’ın resmi sayısal şartı değildir)*
- En az 4 aktif kategori *(proje içi kalite eşiği)*
- Gerçek yazar profilleri
- `/sitemap.xml`, `/robots.txt`, `/ads.txt` erişilebilir
- HTTPS aktif
- CMP yapılandırıldı (AEA/UK/CH trafiği için)

## 7. Onay Öncesinde Reklamları Kapalı Tutun

Varsayılan ayarlar:

- `adsense_ads_enabled` = kapalı
- `adsense_auto_ads_enabled` = kapalı
- Reklam slotları boş

Onay alınmadan reklam kutularını açmayın.

## 8. Onay Sonrası Sınırlı Reklam Gösterimi

1. CMP’nin çalıştığını doğrulayın.
2. Slot ID’lerini admin paneline girin.
3. **Yazı detay sayfalarında reklam kutularını göster** seçeneğini açın.
4. Reklamlar yalnızca şu koşullarda görünür:
   - `APP_ENV=production`
   - Yayınlanmış, en az 900 kelimelik yazı detay sayfası
   - Geçerli client ve slot ID
   - CMP onaylı

Ana sayfa, kategori, etiket, arama, sabit sayfalar, admin, preview ve hata sayfalarında reklam gösterilmez.

## 9. Yasaklı Uygulamalar

- Sahte trafik satın alma
- Kendi reklamlarınıza veya yakın çevrenizin tıklaması
- “Buraya tıklayın”, “Destek olun” gibi tıklama teşvik metinleri
- Reklamları içerik kartı gibi gizleme
- Pop-up, pop-under veya agresif reklam formatları
- Google dışı reklam ağları (bu modül kapsamında)

## 10. Teknik Özet

| Özellik | Davranış |
|---------|----------|
| Doğrulama scripti | `adsense_verification_enabled` + geçerli client ID + production |
| Reklam kutuları | `adsense_ads_enabled` + CMP + production + slot ID |
| Auto Ads | Varsayılan kapalı; ayrı ayar |
| ads.txt | `/ads.txt` — geçerli `pub-` ID gerekir |
| Etiket | Yalnızca “Advertisement” |

Sorular için site iletişim e-postanızı kullanın.
