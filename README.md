**Woocommerce Insider Object Plugin**

**Insider Object Nedir?**

Daha fazla bilgi için [Insider Object Nedir?](#) sayfasına göz atabilirsiniz.

**Kurulum:**

1. [Buradan](https://github.com/egekibar/woocommerce-insider-object-integration/releases) en son sürümü zip olarak indirin.

2. WordPress yönetici paneline gidin ve "Eklentiler" bölümünden "Yeni Eklenti Ekle" seçeneğine tıklayın.

3. "Eklenti Yükleyin" butonuna tıklayarak indirdiğiniz zip dosyasını seçin ve yükleyin.

4. Eklentiyi etkinleştirmek için "Eklentiler" bölümünden "Eklentileri Yönet" sayfasına gidin, "WooCommerce Insider Object Integration" eklentisini bulun ve etkinleştirin.

**Kullanım:**

1. WooCommerce eklentisini etkinleştirdikten sonra, entegrasyon otomatik olarak yapılandırılacaktır.

**Test:**

1. Tarayıcı geliştirici araçlarına (Inspect) gidin ve "Console" sekmesine geçin.

2. Console'a `insider_object` yazın. Eğer bir obje dönerse, test başarılı olmuştur.

---

**Insider Object Integration Hakkında Daha Fazla Bilgi:**

Insider Object Integration Wizard, Insider objesinin alt nesnelerini ve endüstri seçiminize dayalı olarak gerekli anahtarları ve veri türlerini içeren örnek kodları sunar.

**Önerilen Özel Alanlar:**

Sihirbazda sıkça kullanılan alanlara dayalı olarak önerilen özel alanları da görebilirsiniz. Bu özel alanlar, özel nesnenin altına eklenir. Daha fazla özel alan eklemek istiyorsanız, bunları aynı şekilde özel nesnenin altına ekleyebilirsiniz. Özel anahtarlar aşağıdaki gibi belirli olaylara özel kullanıcı özellikleri veya özel etkinlik parametreleri olarak atanır:

| Nesne        | Alan             | Atanır Olarak          |
|--------------|------------------|------------------------|
| Kullanıcı     | Özel nesnenin altındaki anahtar-değer çiftleri | Özel kullanıcı özellikleri |
| Ürün        | Özel nesnenin altındaki anahtar-değer çiftleri | Ürün Detay Sayfası Görüntüleme etkinliği için özel etkinlik parametreleri |
| Sepet       | Özel nesnenin altındaki anahtar-değer çiftleri | Sepet Sayfası Görüntüleme etkinliği için özel etkinlik parametreleri |
| İşlem       | Özel nesnenin altındaki anahtar-değer çiftleri | Satın Alma etkinliği için özel etkinlik parametreleri |

---

*Bu README, proje geliştiricisi [egekibar](https://github.com/egekibar) tarafından oluşturulmuştur.*
