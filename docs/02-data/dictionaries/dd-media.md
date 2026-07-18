# Data Dictionary: media

## Overview
- **Table**: `media` (spatie/laravel-medialibrary)
- **Domain**: Media
- **Purpose**: File storage metadata (images, videos, PDFs, simulations)
- **Owner**: Media Domain Lead
- **Created**: 2024-01-15
- **Approved By**: Architect, PM
- **Version**: 1.0

## Columns

| Column | Type | Constraints | Description | Example |
|--------|------|-------------|-------------|---------|
| id | BIGINT UNSIGNED | PK, AI | Primary key | 1 |
| model_type | VARCHAR(255) | NOT NULL | Polymorphic model | "App\\Models\\Content\\TheoryArticle" |
| model_id | BIGINT UNSIGNED | NOT NULL | Polymorphic ID | 1 |
| uuid | CHAR(36) | NOT NULL, UNIQUE | Global unique ID | "550e8400-e29b..." |
| collection_name | VARCHAR(255) | NOT NULL | Media collection | "theory_images" |
| name | VARCHAR(255) | NOT NULL | Original filename | "bohr-model.png" |
| file_name | VARCHAR(255) | NOT NULL | Stored filename | "bohr-model-550e8400.png" |
| mime_type | VARCHAR(255) | NULLABLE | MIME type | "image/png" |
| disk | VARCHAR(255) | NOT NULL | Storage disk | "s3" |
| conversions_disk | VARCHAR(255) | NULLABLE | Conversion disk | "s3" |
| size | BIGINT UNSIGNED | NOT NULL | Bytes | 245760 |
| manipulations | JSON | NOT NULL | Manipulation queue | [] |
| custom_properties | JSON | NOT NULL | Custom metadata | {"alt": "Bohr model diagram"} |
| generated_conversions | JSON | NOT NULL | Completed conversions | ["thumb", "webp"] |
| responsive_images | JSON | NOT NULL | Responsive variants | [] |
| order_column | INT UNSIGNED | NOT NULL, DEFAULT 0 | Sort order | 1 |
| created_at | TIMESTAMP | NULLABLE | Creation time | 2024-01-15 10:30:00 |
| updated_at | TIMESTAMP | NULLABLE | Last update | 2024-01-15 10:30:00 |

## Indexes

| Name | Columns | Type | Purpose |
|------|---------|------|---------|
| PRIMARY | id | PRIMARY | PK |
| media_model_type_model_id_index | (model_type, model_id) | BTREE | Polymorphic lookup |
| media_uuid_index | uuid | UNIQUE | UUID lookup |

## Collections (configured in models)

| Model | Collection | Conversions |
|-------|------------|-------------|
| TheoryArticle | theory_images | thumb (300w), webp |
| Question | question_media | thumb (200w), webp |
| QuestionOption | option_media | thumb (100w), webp |
| User | avatars | thumb (100x100), circle |
| Exam | exam_media | original |
| TheoryArticle | simulations | original |

## Conversions (defined in model)

| Conversion | Dimensions | Format | Quality |
|------------|------------|--------|---------|
| thumb | 300w x auto | webp | 80 |
| preview | 800w x auto | webp | 85 |
| circle | 100x100 | webp | 80 |

## Business Rules

1. **Managed by spatie/laravel-medialibrary** - do not modify directly
2. **Collections** defined in model's `registerMediaCollections()`
3. **Conversions** auto-generated on upload
4. **Disk**: S3-compatible (MinIO/AWS/R2)
5. **Custom properties** for alt text, caption, credits
6. **Order** for gallery sorting
7. **UUID** for public URLs (not sequential IDs)

## Sample Data

```php
// In TheoryArticle model
public function registerMediaCollections(): void
{
    $this->addMediaCollection('theory_images')
        ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
        ->maxFileSize(5 * 1024 * 1024) // 5MB
        ->singleFile()
        ->registerMediaConversions(function (Media $media) {
            $this->addMediaConversion('thumb')
                ->width(300)
                ->format('webp')
                ->quality(80);
        });
}
```

## Migration Checklist

- [ ] Uses spatie/laravel-medialibrary migration
- [ ] UUID column with unique index
- [ ] Polymorphic indexes on model_type+model_id
- [ ] JSON columns for manipulations/properties/conversions
- [ ] Order column for sorting
- [ ] No soft deletes (library handles)
- [ ] No manual audit fields (library tracks)
- [ ] Tested: `php artisan migrate:fresh --seed`

---

*Generated from Data Dictionary v1.0*