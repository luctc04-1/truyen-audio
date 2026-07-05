<?php

namespace App\Modules\Admin\Controllers;

use App\Models\CrawlJob;
use App\Models\UploadJob;
use App\Modules\Admin\Support\AdminPresenter;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;

class JobAdminController extends BaseController
{
    public function index(): JsonResponse
    {
        $crawlJobs = CrawlJob::query()
            ->orderByDesc('started_at')
            ->limit(20)
            ->get()
            ->map(fn (CrawlJob $job) => AdminPresenter::crawlJob($job));

        $uploadJobs = UploadJob::query()
            ->with('episode:id,title,series_id', 'episode.series:id,title')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn (UploadJob $job) => AdminPresenter::uploadJob($job));

        return $this->success([
            'crawl_jobs'  => $crawlJobs,
            'upload_jobs' => $uploadJobs,
        ]);
    }
}
