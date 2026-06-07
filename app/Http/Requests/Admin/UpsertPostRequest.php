<?php

namespace App\Http\Requests\Admin;

use App\Enums\PostStatus;
use App\Models\Author;
use App\Support\PostQualityChecker;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $post = $this->route('post');
        $postId = $post?->id;
        $requiresPublish = PostQualityChecker::requiresPublishValidation($this->input('status', 'draft'));

        $rules = [
            'author_id' => [$requiresPublish ? 'required' : 'nullable', 'exists:authors,id'],
            'category_id' => [$requiresPublish ? 'required' : 'nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:posts,slug,'.($postId ?? 'NULL')],
            'excerpt' => [$requiresPublish ? 'required' : 'nullable', 'string', 'max:1000'],
            'body' => [$requiresPublish ? 'required' : 'nullable', 'string'],
            'sources' => ['nullable', 'string'],
            'cover_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:5120',
                'extensions:jpg,jpeg,png,webp',
                Rule::requiredIf(fn () => $requiresPublish && ! $post?->cover_image),
            ],
            'cover_image_alt' => [$requiresPublish ? 'required' : 'nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(PostStatus::class)],
            'published_at' => [
                Rule::requiredIf(fn () => $this->input('status') === PostStatus::Scheduled->value),
                'nullable',
                'date',
            ],
            'is_featured' => ['boolean'],
            'meta_title' => [$requiresPublish ? 'required' : 'nullable', 'string', 'max:255'],
            'meta_description' => [$requiresPublish ? 'required' : 'nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'originality_confirmed' => [
                Rule::requiredIf(fn () => $requiresPublish && ! $post?->originality_confirmed_at),
                'boolean',
            ],
            'human_reviewed' => [
                Rule::requiredIf(fn () => $requiresPublish && ! $post?->human_reviewed_at),
                'boolean',
            ],
        ];

        if ($requiresPublish) {
            $rules['title'][] = 'min:'.PostQualityChecker::MIN_TITLE_LENGTH;
            $rules['excerpt'][] = 'min:'.PostQualityChecker::MIN_EXCERPT_LENGTH;
            $rules['excerpt'][] = 'max:'.PostQualityChecker::MAX_EXCERPT_LENGTH;
            $rules['meta_description'][] = 'min:'.PostQualityChecker::MIN_META_DESCRIPTION_LENGTH;
            $rules['meta_description'][] = 'max:'.PostQualityChecker::MAX_META_DESCRIPTION_LENGTH;
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! PostQualityChecker::requiresPublishValidation($this->input('status', 'draft'))) {
                return;
            }

            $wordCount = PostQualityChecker::wordCount($this->input('body'));

            if ($wordCount < PostQualityChecker::MIN_WORD_COUNT) {
                $validator->errors()->add(
                    'body',
                    'Yayın için içerik en az '.PostQualityChecker::MIN_WORD_COUNT.' kelime olmalı (şu an: '.$wordCount.').'
                );
            }

            if ($this->filled('author_id') && ! Author::query()->whereKey($this->input('author_id'))->where('is_active', true)->exists()) {
                $validator->errors()->add('author_id', 'Seçilen yazar aktif olmalıdır.');
            }

            if ($this->boolean('originality_confirmed') === false && ! $this->route('post')?->originality_confirmed_at) {
                $validator->errors()->add('originality_confirmed', 'Özgünlük onayı zorunludur.');
            }

            if ($this->boolean('human_reviewed') === false && ! $this->route('post')?->human_reviewed_at) {
                $validator->errors()->add('human_reviewed', 'İnsan kontrolü onayı zorunludur.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'author_id' => 'yazar',
            'category_id' => 'kategori',
            'title' => 'başlık',
            'excerpt' => 'özet',
            'body' => 'içerik',
            'sources' => 'kaynaklar',
            'cover_image' => 'kapak görseli',
            'cover_image_alt' => 'kapak görseli alt metni',
            'status' => 'durum',
            'published_at' => 'yayın tarihi',
            'meta_title' => 'meta başlık',
            'meta_description' => 'meta açıklama',
            'originality_confirmed' => 'özgünlük onayı',
            'human_reviewed' => 'insan kontrolü onayı',
        ];
    }
}
