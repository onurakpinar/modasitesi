<?php

namespace App\Support\Ads;

use App\Models\Post;
use App\Support\PostQualityChecker;
use App\Support\PublicContent;

class AdEligibility
{
    public static function canShowArticleAds(Post $post): bool
    {
        if (! AdSettings::isProductionEnvironment()) {
            return false;
        }

        if (! AdSettings::adsEnabled()) {
            return false;
        }

        if (! AdSettings::certifiedCmpConfigured()) {
            return false;
        }

        if (AdSettings::clientId() === null) {
            return false;
        }

        if (! PublicContent::postQuery()->whereKey($post->id)->exists()) {
            return false;
        }

        if (PostQualityChecker::wordCount($post->body) < PostQualityChecker::MIN_WORD_COUNT) {
            return false;
        }

        return true;
    }

    public static function canShowMiddleSlot(Post $post): bool
    {
        return self::canShowArticleAds($post)
            && AdSettings::articleMiddleSlot() !== null;
    }

    public static function canShowBottomSlot(Post $post): bool
    {
        return self::canShowArticleAds($post)
            && AdSettings::articleBottomSlot() !== null;
    }
}
