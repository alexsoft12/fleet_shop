<?php

namespace FleetCart\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Translation\Entities\Translation;
use Ramsey\Uuid\Uuid;

class TranslateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $translations = Translation::retrieve();
        foreach ($translations as $key => $value) {
            foreach ($value as $key2 => $value2) {
                if ($key2 == "en" && !isset($value["es"])) {
                    $traduccion = $this->translate_text($value2, $key2, "es");
                    if ($traduccion != null) {
                        Translation::firstOrCreate(['key' => $key])
                            ->translations()
                            ->updateOrCreate(
                                ['locale' => "es"],
                                ['value' => $traduccion]
                            );
                    }
                }
            }
        }
    }

    private function translate_text($text, $from_language, $to_language): ?string
    {
        $config = config('services.microsoft_translator');
        $endpoint = "https://api.cognitive.microsofttranslator.com";

        $client = new Client([
            'base_uri' => $endpoint,
            'headers' => [
                'Ocp-Apim-Subscription-Key' => $config['key'],
                'Ocp-Apim-Subscription-Region' =>  $config['location'],
                'Content-type' => 'application/json',
                'X-ClientTraceId' => Uuid::uuid4()->toString()
            ]
        ]);

        $response = $client->request('POST', '/translate', [
            'query' => [
                'api-version' => '3.0',
                'from' => $from_language,
                'to' => [$to_language]
            ],
            'json' => [
                [
                    'text' => $text
                ]
            ]
        ]);

        return json_decode($response->getBody())[0]->translations[0]->text ?? null;
    }
}
