<?php

namespace Database\Factories;

use App\Models\Email;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailFactory extends Factory
{
    protected $model = Email::class;

    public function definition(): array
    {
        $receivedAt = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'message_id' => '<' . $this->faker->uuid() . '@' . $this->faker->domainName() . '>',
            'from_address' => $this->faker->safeEmail(),
            'from_name' => $this->faker->name(),
            'to_addresses' => [$this->faker->safeEmail()],
            'cc_addresses' => $this->faker->boolean(20) ? [$this->faker->safeEmail()] : null,
            'subject' => $this->faker->randomElement([
                'Invoice #' . $this->faker->numberBetween(1000, 9999),
                'Re: ' . $this->faker->sentence(4),
                'Meeting Tomorrow at ' . $this->faker->time('g:i A'),
                $this->faker->sentence(6),
                'Weekly Report - ' . $this->faker->date('M d, Y'),
                'Important: ' . $this->faker->sentence(3),
                'Fw: ' . $this->faker->sentence(5),
                'Order Confirmation #' . $this->faker->numerify('######'),
                'Welcome to ' . $this->faker->company(),
                'Your account update',
                'Action Required: ' . $this->faker->sentence(3),
                'Newsletter - ' . $this->faker->monthName() . ' Edition',
            ]),
            'text_body' => $this->faker->paragraphs(3, true),
            'html_body' => '<div style="font-family: Arial, sans-serif;">'
                . '<p>' . $this->faker->paragraph() . '</p>'
                . '<p>' . $this->faker->paragraph() . '</p>'
                . '<p>Best regards,<br>' . $this->faker->name() . '</p>'
                . '</div>',
            'sender_ip' => $this->faker->ipv4(),
            'spam_score' => $this->faker->randomFloat(1, 0, 8),
            'attachment_count' => 0,
            'is_read' => $this->faker->boolean(60),
            'is_starred' => $this->faker->boolean(15),
            'is_spam' => $this->faker->boolean(5),
            'labels' => $this->faker->boolean(30) ? $this->faker->randomElements(['important', 'work', 'personal', 'finance', 'newsletter'], rand(1, 2)) : null,
            'status' => 'processed',
            'received_at' => $receivedAt,
        ];
    }

    public function unread(): static
    {
        return $this->state(fn() => ['is_read' => false]);
    }

    public function starred(): static
    {
        return $this->state(fn() => ['is_starred' => true]);
    }

    public function spam(): static
    {
        return $this->state(fn() => ['is_spam' => true, 'spam_score' => $this->faker->randomFloat(1, 5, 10)]);
    }

    public function withAttachments(int $count = 1): static
    {
        return $this->state(fn() => ['attachment_count' => $count]);
    }
}
