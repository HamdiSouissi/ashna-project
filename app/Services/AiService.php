<?php
declare(strict_types=1);
namespace App\Services;

class AiService
{
    private bool $enabled;
    private string $baseUrl;
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->enabled = setting('ai_enabled', '0') === '1'
            && (string)setting('ai_base_url', '') !== '';
        $this->baseUrl = rtrim((string)setting('ai_base_url', ''), '/');
        $this->apiKey  = (string)setting('ai_api_key', '');
        $this->model   = (string)setting('ai_model', LF_CONFIG['ai']['model']);
    }

    public function enabled(): bool { return $this->enabled; }

    /**
     * Appel a un endpoint compatible OpenAI (/chat/completions).
     * @param array<int,array{role:string,content:string}> $messages
     */
    private function chat(array $messages): string
    {
        $res = HttpClient::post($this->baseUrl . '/chat/completions', [
            'body' => json_encode([
                'model'       => $this->model,
                'messages'    => $messages,
                'temperature' => 0.6,
                'max_tokens'  => 600,
            ], JSON_UNESCAPED_UNICODE),
            'headers' => array_filter([
                'Content-Type'  => 'application/json',
                'Authorization' => $this->apiKey !== '' ? 'Bearer ' . $this->apiKey : null,
            ]),
            'user_agent' => 'LeadFinder/1.0',
            'timeout'    => (int)LF_CONFIG['ai']['timeout'],
        ]);
        if ($res['status'] !== 200) {
            throw new \RuntimeException('IA indisponible (HTTP ' . $res['status'] . ' ' . $res['error'] . ')');
        }
        $json = json_decode($res['body'], true);
        $content = $json['choices'][0]['message']['content'] ?? null;
        if (!is_string($content)) {
            throw new \RuntimeException('Reponse IA invalide.');
        }
        return trim($content);
    }

    /** (a) Normalise telephone et adresse. @return array{telephone?:string,adresse?:string} */
    public function normalizeContact(array $p): array
    {
        $raw = trim(($p['telephone'] ?: '') . ' | ' . ($p['adresse'] ?: '') . ' | ' . $p['ville']);
        $answer = $this->chat([
            ['role' => 'system', 'content' => 'Tu es un assistant qui normalise des coordonnees commerciales. Reponds STRICTEMENT en JSON {"telephone":"+...","adresse":"..."} sans aucun autre texte. Le telephone doit etre au format E.164 (indicatif ' . setting('default_country', 'TN') . '). Si une valeur est introuvable, renvoie une chaine vide pour ce champ.'],
            ['role' => 'user', 'content' => $raw],
        ]);
        $json = json_decode(preg_replace('/^```(?:json)?|```$/m', '', $answer) ?: $answer, true);
        return is_array($json) ? array_intersect_key($json, ['telephone' => 1, 'adresse' => 1]) : [];
    }

    /** (b) Redige un message d'approche personnalise. */
    public function generateMessage(array $p, string $channel, string $lang): string
    {
        $limits = ['whatsapp' => 400, 'sms' => 300, 'email' => 900];
        $max = $limits[$channel] ?? 400;
        $context = sprintf(
            "Nom: %s\nCategorie: %s\nAdresse: %s, %s\nTelephone: %s",
            $p['nom'], $p['categorie'], $p['adresse'], $p['ville'], $p['telephone_normalise'] ?: $p['telephone']
        );
        return $this->chat([
            ['role' => 'system', 'content' => "Tu es un commercial expert en creation de sites web pour petits commerces. Redige un message d'approche personnalise, chaleureux et professionnel, en " . ($lang === 'ar' ? 'arabe' : 'francais') . ", pour proposer la creation d'un site web. Ce commerce n'a pas de site. Maximum $max caracteres. Sortie: UNIQUEMENT le message final, sans guillemets ni preambule."],
            ['role' => 'user', 'content' => $context . "\nCanal: " . $channel],
        ]);
    }
}
