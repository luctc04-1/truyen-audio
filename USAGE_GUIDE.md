# 📚 Hướng Dẫn Sử Dụng - Truyen Audio Architecture

## 1️⃣ Tạo Module Mới

### Bước 1: Tạo thư mục module
```
app/Modules/YourModule/
├── Controllers/
├── Repositories/
│   ├── Contracts/
│   └── Eloquent/
├── Models/
├── Services/
└── Requests/
```

### Bước 2: Tạo Repository Interface
`app/Modules/YourModule/Repositories/Contracts/YourRepositoryInterface.php`:
```php
<?php
namespace App\Modules\YourModule\Repositories\Contracts;

interface YourRepositoryInterface
{
    public function getAll($columns = ['*']);
    public function getById($id);
    public function create($data);
    public function update($id, $data);
    public function delete($id);
}
```

### Bước 3: Tạo Repository Implementation
`app/Modules/YourModule/Repositories/Eloquent/YourRepository.php`:
```php
<?php
namespace App\Modules\YourModule\Repositories\Eloquent;

use App\Modules\YourModule\Models\Your;
use App\Modules\YourModule\Repositories\Contracts\YourRepositoryInterface;

class YourRepository implements YourRepositoryInterface
{
    protected $model;

    public function __construct(Your $model)
    {
        $this->model = $model;
    }

    public function getAll($columns = ['*'])
    {
        return $this->model->select($columns)->get();
    }

    public function getById($id)
    {
        return $this->model->find($id);
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function update($id, $data)
    {
        $item = $this->model->find($id);
        if ($item) {
            $item->update($data);
        }
        return $item;
    }

    public function delete($id)
    {
        return $this->model->destroy($id) > 0;
    }
}
```

### Bước 4: Tạo Controller
`app/Modules/YourModule/Controllers/YourController.php`:
```php
<?php
namespace App\Modules\YourModule\Controllers;

use App\Shared\Controllers\BaseController;
use App\Modules\YourModule\Repositories\Contracts\YourRepositoryInterface;

class YourController extends BaseController
{
    protected $repository;

    public function __construct(YourRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $items = $this->repository->getAll();
        return $this->success($items, 'Items retrieved successfully');
    }

    public function show($id)
    {
        $item = $this->repository->getById($id);
        if (!$item) {
            return $this->error('Item not found', 404);
        }
        return $this->success($item);
    }
}
```

### Bước 5: Bind Repository
Thêm vào `app/Providers/RepositoryServiceProvider.php`:
```php
$this->app->bind(
    YourRepositoryInterface::class,
    YourRepository::class
);
```

---

## 2️⃣ Sử Dụng BaseController

```php
// Success response
return $this->success($data, 'Success message', 200);

// Error response  
return $this->error('Error message', 400);
```

Response format:
```json
{
    "success": true,
    "message": "Success message",
    "data": { ... }
}
```

---

## 3️⃣ Sử Dụng Traits

### ResponseTrait
```php
use App\Shared\Traits\ResponseTrait;

class MyController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        $items = Item::paginate(15);
        return $this->paginate($items, 'Items retrieved');
    }
}
```

---

## 4️⃣ Sử Dụng Helpers

### StringHelper
```php
use App\Shared\Helpers\StringHelper;

// Convert to slug
$slug = StringHelper::toSlug('Hello World');  // 'hello-world'

// Truncate string
$text = StringHelper::truncate('Long text...', 50);

// Generate random
$token = StringHelper::random(32);
```

---

## 5️⃣ Sử Dụng Enums

```php
use App\Shared\Enums\StatusEnum;

// Use enum
$status = StatusEnum::ACTIVE;
echo $status->value;  // 'active'

// Get all values
$values = StatusEnum::values();  // ['active', 'inactive', 'draft', 'published', 'archived']
```

---

## 6️⃣ Sử Dụng DTOs

```php
use App\Shared\DTOs\PaginationDTO;

$pagination = new PaginationDTO(
    page: 1,
    perPage: 15,
    sortBy: 'created_at',
    sortDirection: 'desc'
);

$items = Item::offset($pagination->offset())
    ->limit($pagination->perPage)
    ->orderBy($pagination->sortBy, $pagination->sortDirection)
    ->get();
```

---

## 7️⃣ Frontend: Sử Dụng Stores

### Auth Store
```javascript
import { useAuthStore } from '@/stores/authStore';

const auth = useAuthStore();

// Login
await auth.login('email@example.com', 'password');

// Check if authenticated
if (auth.isAuthenticated) {
    console.log(auth.user);
}

// Logout
auth.logout();
```

### Audio Store
```javascript
import { useAudioStore } from '@/stores/audioStore';

const audio = useAudioStore();

// Set playlist
audio.setPlaylist(tracks);

// Play
audio.play(track);

// Check current track
console.log(audio.currentTrackData);

// Next/Previous
audio.nextTrack();
audio.previousTrack();
```

---

## 8️⃣ Frontend: Sử Dụng Services

### API Service
```javascript
import ApiService from '@/services/ApiService';

// GET
const data = await ApiService.get('/stories');
const item = await ApiService.get('/stories/1', { fields: 'id,title' });

// POST
const created = await ApiService.post('/stories', {
    title: 'New Story',
    description: 'Description'
});

// PUT
const updated = await ApiService.put('/stories/1', {
    title: 'Updated Title'
});

// DELETE
await ApiService.delete('/stories/1');
```

### Story Service
```javascript
import StoryService from '@/services/StoryService';

const stories = await StoryService.getStories({ page: 1 });
const story = await StoryService.getStory(1);
const created = await StoryService.createStory({...});
const updated = await StoryService.updateStory(1, {...});
await StoryService.deleteStory(1);
```

### Audio Service
```javascript
import AudioService from '@/services/AudioService';

const files = await AudioService.getAudioFiles();
const file = await AudioService.getAudioFile(1);

// Upload with FormData
const formData = new FormData();
formData.append('file', fileInput.files[0]);
await AudioService.uploadAudio(formData);
```

---

## 9️⃣ Frontend: Sử Dụng Composables

### useLoading
```javascript
import { useLoading } from '@/composables/useLoading';

export default {
    setup() {
        const { loading, withLoading } = useLoading();

        const fetchData = async () => {
            await withLoading(async () => {
                // Your async code here
                const data = await fetch(...);
            });
        };

        return { loading, fetchData };
    }
}
```

### useError
```javascript
import { useError } from '@/composables/useError';

export default {
    setup() {
        const { error, handleError, clearError } = useError();

        const doSomething = async () => {
            try {
                // Your code
            } catch (err) {
                handleError(err);
            }
        };

        return { error, handleError, clearError, doSomething };
    }
}
```

---

## 🔟 Frontend: Sử Dụng Utils

```javascript
import { formatTime, formatBytes, debounce, throttle } from '@/utils/helpers';

// Format time
console.log(formatTime(125));  // '2:05'
console.log(formatTime(3665));  // '1:01:05'

// Format bytes
console.log(formatBytes(1024));  // '1 KB'
console.log(formatBytes(1048576));  // '1 MB'

// Debounce
const search = debounce((query) => {
    // Make search request
}, 300);

input.addEventListener('input', (e) => search(e.target.value));

// Throttle
const scroll = throttle(() => {
    // Handle scroll
}, 1000);

window.addEventListener('scroll', scroll);
```

---

## 🎯 Constants

```php
use App\Shared\Constants\ApiConstants;

// Messages
ApiConstants::MSG_SUCCESS           // 'Operation successful'
ApiConstants::MSG_ERROR             // 'Operation failed'
ApiConstants::MSG_NOT_FOUND         // 'Resource not found'
ApiConstants::MSG_UNAUTHORIZED      // 'Unauthorized'
ApiConstants::MSG_VALIDATION_ERROR  // 'Validation error'

// HTTP Status Codes
ApiConstants::HTTP_OK           // 200
ApiConstants::HTTP_CREATED      // 201
ApiConstants::HTTP_BAD_REQUEST  // 400
ApiConstants::HTTP_UNAUTHORIZED // 401
ApiConstants::HTTP_NOT_FOUND    // 404
ApiConstants::HTTP_INTERNAL_ERROR // 500

// Pagination
ApiConstants::DEFAULT_PAGE      // 1
ApiConstants::DEFAULT_PER_PAGE  // 15
ApiConstants::MAX_PER_PAGE      // 100
```

---

## 📋 Checklist - Các Bước Tiếp Theo

- [ ] Cài đặt Laravel Breeze hoặc Sanctum
- [ ] Hoàn chỉnh AuthController
- [ ] Tạo database migrations
- [ ] Tạo Chapter, Audio repositories (theo pattern Story)
- [ ] Cấu hình routes
- [ ] Tạo Vue pages
- [ ] Tạo Vue components
- [ ] Kết nối frontend với backend API

---

**Last Updated**: 2024
**Version**: 1.0
