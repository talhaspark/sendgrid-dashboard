<?php

namespace Database\Factories;

use App\Models\Email;
use App\Models\EmailAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailAttachmentFactory extends Factory
{
    protected $model = EmailAttachment::class;

    public function definition(): array
    {
        $types = [
            ['ext' => 'pdf', 'mime' => 'application/pdf', 'prefix' => 'document'],
            ['ext' => 'xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'prefix' => 'spreadsheet'],
            ['ext' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'prefix' => 'report'],
            ['ext' => 'png', 'mime' => 'image/png', 'prefix' => 'screenshot'],
            ['ext' => 'jpg', 'mime' => 'image/jpeg', 'prefix' => 'photo'],
            ['ext' => 'csv', 'mime' => 'text/csv', 'prefix' => 'data'],
            ['ext' => 'zip', 'mime' => 'application/zip', 'prefix' => 'archive'],
            ['ext' => 'txt', 'mime' => 'text/plain', 'prefix' => 'notes'],
        ];

        $type = $this->faker->randomElement($types);
        $filename = $this->faker->uuid() . '.' . $type['ext'];

        return [
            'email_id' => Email::factory(),
            'filename' => $filename,
            'original_filename' => $type['prefix'] . '_' . $this->faker->numerify('####') . '.' . $type['ext'],
            'mime_type' => $type['mime'],
            'size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'storage_path' => 'attachments/' . now()->format('Y/m') . '/' . $filename,
            'disk' => 'local',
            'checksum' => md5($this->faker->uuid()),
            'is_inline' => false,
            'is_scanned' => $this->faker->boolean(80),
            'is_clean' => true,
        ];
    }
}
