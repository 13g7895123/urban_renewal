# 後端欄位映射問題報告

> 建立日期：2025-12-16
> 更新日期：2025-12-16
> 狀態：待修復

## 1. 問題概述

在比對 API 文件與後端實體（Entity）後，發現「更新會管理 (Urban Renewal)」模組存在欄位映射缺失問題。資料庫欄位已存在，但後端 Entity 與 Service 層未正確讀取與儲存這些欄位，導致前端傳送的資料無法正確被處理。

## 2. 欄位對照與問題說明

### 2.1 理事長姓名 (`chairman_name`)

| 項目 | 說明 |
|------|------|
| **API 文件定義** | `chairman_name` (string) |
| **資料庫欄位** | ✅ 已存在 |
| **Entity 現況** | ❌ 未定義此屬性 |
| **Service 現況** | ❌ 未處理此欄位 |

**說明**：理事長 (`chairman`) 與聯絡人 (`contact_person`) 是**不同角色**，不應混用。Entity 需新增 `chairmanName` 屬性並映射至資料庫的 `chairman_name` 欄位。

---

### 2.2 理事長電話 (`chairman_phone`)

| 項目 | 說明 |
|------|------|
| **API 文件定義** | `chairman_phone` (string) |
| **資料庫欄位** | ✅ 已存在 |
| **Entity 現況** | ❌ 未定義此屬性 |
| **Service 現況** | ❌ 未處理此欄位 |

**說明**：與 `chairman_name` 相同，此欄位需在 Entity 中獨立定義。

---

### 2.3 代表人 (`representative`)

| 項目 | 說明 |
|------|------|
| **API 文件定義** | `representative` (string, 選填) |
| **資料庫欄位** | ✅ 已存在 |
| **Entity 現況** | ❌ 未定義此屬性 |
| **Service 現況** | ❌ 未處理此欄位 |

---

## 3. 修復建議

### 3.1 修改 `App\Entities\UrbanRenewal`

新增以下屬性與 Getter/Setter：

```php
private ?string $chairmanName = null;
private ?string $chairmanPhone = null;
private ?string $representative = null;

// Getters
public function getChairmanName(): ?string { return $this->chairmanName; }
public function getChairmanPhone(): ?string { return $this->chairmanPhone; }
public function getRepresentative(): ?string { return $this->representative; }

// Setters
public function setChairmanName(?string $name): self { $this->chairmanName = $name; return $this; }
public function setChairmanPhone(?string $phone): self { $this->chairmanPhone = $phone; return $this; }
public function setRepresentative(?string $rep): self { $this->representative = $rep; return $this; }
```

更新 `toArray()` 方法：
```php
'chairman_name' => $this->chairmanName,
'chairman_phone' => $this->chairmanPhone,
'representative' => $this->representative,
```

更新 `fromArray()` 方法：
```php
$entity->setChairmanName($data['chairman_name'] ?? null);
$entity->setChairmanPhone($data['chairman_phone'] ?? null);
$entity->setRepresentative($data['representative'] ?? null);
```

---

### 3.2 修改 `App\Repositories\UrbanRenewalRepository`

更新 `dehydrate()` 方法：
```php
'chairman_name' => $entity->getChairmanName(),
'chairman_phone' => $entity->getChairmanPhone(),
'representative' => $entity->getRepresentative(),
```

---

### 3.3 修改 `App\Services\UrbanRenewalService`

在 `create()` 方法中新增：
```php
$entity->setChairmanName($data['chairman_name'] ?? null);
$entity->setChairmanPhone($data['chairman_phone'] ?? null);
$entity->setRepresentative($data['representative'] ?? null);
```

在 `update()` 方法中新增：
```php
if (array_key_exists('chairman_name', $data)) {
    $entity->setChairmanName($data['chairman_name']);
}
if (array_key_exists('chairman_phone', $data)) {
    $entity->setChairmanPhone($data['chairman_phone']);
}
if (array_key_exists('representative', $data)) {
    $entity->setRepresentative($data['representative']);
}
```

---

## 4. 欄位角色澄清

| 欄位 | 中文名稱 | 說明 |
|------|----------|------|
| `chairman_name` | 理事長姓名 | 更新會的理事長，負責決策與代表更新會 |
| `chairman_phone` | 理事長電話 | 理事長的聯絡電話 |
| `contact_person` | 聯絡人 | 一般行政聯絡窗口，可能是秘書或行政人員 |
| `representative` | 代表人 | 法定代表人（可能與理事長相同或不同） |
| `phone` | 更新會電話 | 更新會的公用電話 |
