<?php

namespace App\Jobs;

use App\Models\Pornstar;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncPornstarFromMessage
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $attrs = $this->data;

            $pornstar = Pornstar::updateOrCreate(
                ['external_id' => $attrs['id']],
                [
                    'name' => $attrs['name'] ?? 'Unknown',
                    'link' => $attrs['link'] ?? 'https://example.com',
                    'license' => $attrs['license'] ?? null,
                    'wl_status' => $attrs['wlStatus'] ?? false,

                    'hair_color' => $attrs['attributes']['hairColor'] ?? null,
                    'ethnicity' => $attrs['attributes']['ethnicity'] ?? null,
                    'has_tattoos' => $attrs['attributes']['tattoos'] ?? false,
                    'has_piercings' => $attrs['attributes']['piercings'] ?? false,
                    'breast_size' => $attrs['attributes']['breastSize'] ?? null,
                    'breast_type' => $attrs['attributes']['breastType'] ?? null,
                    'gender' => $attrs['attributes']['gender'] ?? null,
                    'orientation' => $attrs['attributes']['orientation'] ?? null,
                    'age' => $attrs['attributes']['age'] ?? null,

                    'subscriptions' => $attrs['attributes']['stats']['subscriptions'] ?? null,
                    'monthly_searches' => $attrs['attributes']['stats']['monthlySearches'] ?? null,
                    'views' => $attrs['attributes']['stats']['views'] ?? null,
                    'videos_count' => $attrs['attributes']['stats']['videosCount'] ?? null,
                    'premium_videos_count' => $attrs['attributes']['stats']['premiumVideosCount'] ?? null,
                    'white_label_video_count' => $attrs['attributes']['stats']['whiteLabelVideoCount'] ?? null,
                    'rank' => $attrs['attributes']['stats']['rank'] ?? null,
                    'rank_premium' => $attrs['attributes']['stats']['rankPremium'] ?? null,
                    'rank_wl' => $attrs['attributes']['stats']['rankWl'] ?? null,
                ]
            );

            if (isset($attrs['aliases']) && is_array($attrs['aliases'])) {
                $existingAliases = $pornstar->aliases()->pluck('alias')->toArray();
                $newAliases = array_diff($attrs['aliases'], $existingAliases);

                foreach ($newAliases as $alias) {
                    $pornstar->aliases()->create(['alias' => $alias]);
                }
            }

            if (isset($attrs['thumbnails']) && is_array($attrs['thumbnails'])) {
                foreach ($attrs['thumbnails'] as $thumbData) {
                    $thumbnail = $pornstar->thumbnails()->firstOrCreate([
                        'type' => $thumbData['type'] ?? null,
                        'width' => $thumbData['width'] ?? null,
                        'height' => $thumbData['height'] ?? null,
                    ]);

                    foreach ($thumbData['urls'] ?? [] as $url) {
                        $thumbnail->urls()->firstOrCreate([
                            'url' => $url,
                        ]);
                    }
                }
            }
        }, 3); // Retry on deadlock
    }
}
