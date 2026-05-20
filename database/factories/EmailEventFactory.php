<?php

namespace Database\Factories;

use App\Models\Email;
use App\Models\EmailEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailEventFactory extends Factory
{
    protected $model = EmailEvent::class;

    public function definition(): array
    {
        $eventTypes = ['processed', 'delivered', 'open', 'click', 'bounce', 'dropped', 'deferred', 'spam_report'];
        $eventType = $this->faker->randomElement($eventTypes);

        return [
            'email_id' => Email::factory(),
            'sg_message_id' => $this->faker->uuid(),
            'event_type' => $eventType,
            'email_address' => $this->faker->safeEmail(),
            'event_timestamp' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'smtp_id' => '<' . $this->faker->uuid() . '@ismtpd.sendgrid.net>',
            'sg_event_id' => $this->faker->uuid(),
            'reason' => in_array($eventType, ['bounce', 'dropped']) ? $this->faker->sentence() : null,
            'status' => $eventType === 'bounce' ? '550' : null,
            'response' => $eventType === 'delivered' ? '250 OK' : null,
            'url' => $eventType === 'click' ? $this->faker->url() : null,
            'useragent' => in_array($eventType, ['open', 'click']) ? $this->faker->userAgent() : null,
            'ip' => $this->faker->ipv4(),
            'raw_payload' => null,
        ];
    }
}
