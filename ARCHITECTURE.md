# Cấu Trúc Source Code - Truyen Audio

Tài liệu này mô tả cấu trúc toàn bộ dự án Truyen Audio sau khi thiết kế lại theo mô hình Module-Based Architecture.

## Tổng Quan Cấu Trúc

```
truyen-audio/
├── app/
│   ├── Modules/
│   │   ├── Auth/              # Xác thực
│   │   ├── User/              # Quản lý người dùng
│   │   ├── Story/             # Quản lý truyện
│   │   ├── Chapter/           # Quản lý chương
│   │   ├── Audio/             # Quản lý file audio
│   │   └── Admin/             # Admin panel
│   ├── Shared/                # Shared layer (Controllers, Services, Traits, DTOs, etc)
│   └── Providers/             # Service Providers
│
├── resources/
│   ├── js/
│   │   ├── pages/             # Vue pages
│   │   ├── components/        # Vue components
│   │   ├── stores/            # Pinia stores (auth, audio)
│   │   ├── services/          # API services
│   │   ├── composables/       # Vue composables
│   │   └── utils/             # Utility functions
│   └── css/
│
├── routes/
├── config/
├── database/
└── ...
```

---

## Chi Tiết Cấu Trúc

### 1. **app/Modules/** - Module Layer

Mỗi module chứa toàn bộ logic của một tính năng:

#### **Auth Module** (`app/Modules/Auth/`)
- `Controllers/AuthController.php` - Xử lý login, register, logout
- `Services/` - Business logic cho xác thực
- `Requests/` - Form request validation

#### **User Module** (`app/Modules/User/`)
- `Controllers/UserController.php` - API endpoints
- `Repositories/` - Repository pattern (Interface + Eloquent)
- `Models/` - User model
- `Services/` - Business logic
- `Requests/` - Form validation

#### **Story Module** (`app/Modules/Story/`)
- `Controllers/StoryController.php` - Toàn bộ CRUD
- `Repositories/`
  - `Contracts/StoryRepositoryInterface.php`
  - `Eloquent/StoryRepository.php`
- `Models/Story.php`
- `Services/StoryService.php`
- `Requests/` - Form validation

#### **Chapter Module** (`app/Modules/Chapter/`)
- Tương tự Story Module

#### **Audio Module** (`app/Modules/Audio/`)
- Xử lý upload, download audio
- `Repositories/` - Audio repository pattern
- `Services/` - Audio processing logic

#### **Admin Module** (`app/Modules/Admin/`)
- `Controllers/`
  - `Dashboard/` - Dashboard controller
  - `Story/` - Story management
  - `Chapter/` - Chapter management
  - `Audio/` - Audio management
  - `Category/` - Category management
  - `User/` - User management
  - `Settings/` - Settings management

---

### 2. **app/Shared/** - Shared Layer

Chứa tất cả code được sử dụng lại:

#### **Controllers** (`Shared/Controllers/`)
- `BaseController.php` - Base class với methods `success()`, `error()`

#### **Services** (`Shared/Services/`)
- Các service chung không liên quan đến module cụ thể

#### **Traits** (`Shared/Traits/`)
- `ResponseTrait.php` - Các method response (pagination, timestamp)

#### **Helpers** (`Shared/Helpers/`)
- `StringHelper.php` - Hàm xử lý string (slug, truncate, random)

#### **DTOs** (`Shared/DTOs/`)
- `PaginationDTO.php` - DTO cho pagination

#### **Enums** (`Shared/Enums/`)
- `StatusEnum.php` - Enum cho status (active, inactive, draft, published, archived)

#### **Exceptions** (`Shared/Exceptions/`)
- `ApiException.php` - Custom exception class

#### **Constants** (`Shared/Constants/`)
- `ApiConstants.php` - Các hằng số (messages, HTTP codes, pagination)

---

### 3. **app/Providers/** - Service Providers

#### **RepositoryServiceProvider.php**
Bind các repository interfaces:
```php
$this->app->bind(
    StoryRepositoryInterface::class,
    StoryRepository::class
);
```

---

### 4. **resources/js/** - Frontend Vue

#### **stores/** - Pinia Stores
- `authStore.js` - Quản lý auth state
- `audioStore.js` - Quản lý audio player state

#### **services/** - API Services
- `ApiService.js` - Base service (GET, POST, PUT, DELETE)
- `StoryService.js` - Story API calls
- `AudioService.js` - Audio API calls

#### **composables/** - Vue Composables
- `useLoading.js` - Loading state logic
- `useError.js` - Error handling logic

#### **utils/** - Utility Functions
- `helpers.js` - formatTime, formatBytes, debounce, throttle

#### **pages/** - Page Components
- Sắp được tạo khi cần

#### **components/** - Reusable Components
- Sắp được tạo khi cần

---

## Repository Pattern

### Ví Dụ: Story Module

**Interface** (`Repositories/Contracts/StoryRepositoryInterface.php`):
```php
interface StoryRepositoryInterface {
    public function getAll($columns = ['*']);
    public function getById($id);
    public function create($data);
    public function update($id, $data);
    public function delete($id);
}
```

**Implementation** (`Repositories/Eloquent/StoryRepository.php`):
```php
class StoryRepository implements StoryRepositoryInterface {
    protected $model;
    
    public function __construct(Story $model) {
        $this->model = $model;
    }
    
    public function getAll($columns = ['*']) {
        return $this->model->select($columns)->get();
    }
    // ... other methods
}
```

**Controller Usage**:
```php
class StoryController extends BaseController {
    protected $storyRepository;
    
    public function __construct(StoryRepositoryInterface $storyRepository) {
        $this->storyRepository = $storyRepository;
    }
    
    public function index() {
        $stories = $this->storyRepository->getAll();
        return $this->success($stories);
    }
}
```

---

## Response Format

Tất cả API responses đều theo format:

**Success:**
```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

**Error:**
```json
{
    "success": false,
    "message": "Error message",
    "errors": { ... }
}
```

---

## File Đã Được Tạo

### PHP Backend
- ✅ `app/Shared/Controllers/BaseController.php`
- ✅ `app/Shared/Exceptions/ApiException.php`
- ✅ `app/Shared/Enums/StatusEnum.php`
- ✅ `app/Shared/Constants/ApiConstants.php`
- ✅ `app/Shared/Traits/ResponseTrait.php`
- ✅ `app/Shared/Helpers/StringHelper.php`
- ✅ `app/Shared/DTOs/PaginationDTO.php`
- ✅ `app/Modules/Story/Repositories/Contracts/StoryRepositoryInterface.php`
- ✅ `app/Modules/Story/Repositories/Eloquent/StoryRepository.php`
- ✅ `app/Modules/Story/Models/Story.php`
- ✅ `app/Modules/Story/Controllers/StoryController.php`
- ✅ `app/Modules/Auth/Controllers/AuthController.php`
- ✅ `app/Modules/User/Repositories/Contracts/UserRepositoryInterface.php`
- ✅ `app/Modules/User/Repositories/Eloquent/UserRepository.php`
- ✅ `app/Providers/RepositoryServiceProvider.php`

### Vue Frontend
- ✅ `resources/js/stores/authStore.js`
- ✅ `resources/js/stores/audioStore.js`
- ✅ `resources/js/services/ApiService.js`
- ✅ `resources/js/services/StoryService.js`
- ✅ `resources/js/services/AudioService.js`
- ✅ `resources/js/composables/useLoading.js`
- ✅ `resources/js/composables/useError.js`
- ✅ `resources/js/utils/helpers.js`

---

## Các Bước Tiếp Theo

### Phase 5: Authentication
1. Cài `composer require laravel/breeze` hoặc `laravel/sanctum`
2. Hoàn chỉnh `AuthController.php`
3. Tạo migration cho bảng users

### Phase 6: Database Migrations
Tạo migrations cho:
- users
- authors
- categories, tags
- stories, story_categories, story_tags
- chapters
- audio_files
- favorites, playlists, playlist_items
- comments, ratings
- listen_histories
- notifications

### Phase 7: Routes
Thiết lập routes trong `routes/web.php` và `routes/api.php`

### Phase 8: Frontend Components
Tạo Vue components cho:
- Pages (Home, Library, StoryDetail, etc)
- Components (AudioPlayer, StoryCard, etc)

---

## Quy Ước Code

### Naming Conventions
- **Controllers**: `*Controller.php` (e.g., `StoryController.php`)
- **Models**: Singular name (e.g., `Story.php`)
- **Repositories**: `*Repository.php`
- **Interfaces**: `*Interface.php`
- **Services**: `*Service.php`
- **Traits**: Use past participle (e.g., `ResponseTrait.php`)
- **Enums**: `*Enum.php`

### Code Organization
- Một module = một feature
- Repository pattern cho database access
- Dependency injection cho loose coupling
- Trait cho code reuse
- Consistent response format

---

## Chạy Dự Án

```bash
# Cài dependencies
composer install
npm install

# Chạy migrations
php artisan migrate

# Chạy dev server
php artisan serve

# Build frontend
npm run dev
```

---

**Ngày tạo**: 2024
**Version**: 1.0
**Status**: ✅ Structure Completed
