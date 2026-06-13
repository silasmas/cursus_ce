<?php

namespace App\Services\Ai;

use App\Exceptions\AiWriterException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use PdroLucas\FilamentAiWriter\Contracts\AiProvider;

/**
 * Encapsule le provider IA et transforme les erreurs HTTP en messages lisibles.
 */
class SafeAiProvider implements AiProvider
{
  /**
   * @param  AiProvider  $inner  Provider OpenAI, Anthropic ou Gemini
   */
  public function __construct(
    private readonly AiProvider $inner,
  ) {
  }

  /**
   * Génère du texte via le provider configuré.
   *
   * @param  string  $systemPrompt  Consignes système
   * @param  string  $userInput  Demande de l'utilisateur
   * @return string Texte généré
   *
   * @throws AiWriterException Erreur affichable dans Filament
   */
  public function generate(string $systemPrompt, string $userInput): string
  {
    $providerName = (string) config('filament-ai-writer.provider', 'openai');
    $apiKey = config("filament-ai-writer.{$providerName}.api_key");

    if (blank($apiKey)) {
      throw new AiWriterException(
        __('ai-writer.errors.missing_api_key_title'),
        __('ai-writer.errors.missing_api_key_body'),
      );
    }

    try {
      return $this->inner->generate($systemPrompt, $userInput);
    } catch (RequestException $exception) {
      throw $this->mapRequestException($exception);
    } catch (ConnectionException) {
      throw new AiWriterException(
        __('ai-writer.errors.timeout_title'),
        __('ai-writer.errors.timeout_body'),
      );
    }
  }

  /**
   * Convertit une erreur HTTP en exception métier.
   *
   * @throws AiWriterException
   */
  private function mapRequestException(RequestException $exception): AiWriterException
  {
    $status = $exception->response?->status();

    return match ($status) {
      401 => new AiWriterException(
        __('ai-writer.errors.unauthorized_title'),
        __('ai-writer.errors.unauthorized_body'),
        401,
      ),
      403 => new AiWriterException(
        __('ai-writer.errors.forbidden_title'),
        __('ai-writer.errors.forbidden_body'),
        403,
      ),
      429 => new AiWriterException(
        __('ai-writer.errors.rate_limit_title'),
        __('ai-writer.errors.rate_limit_body'),
        429,
      ),
      default => new AiWriterException(
        __('ai-writer.errors.generic_title'),
        __('ai-writer.errors.generic_body'),
        (int) ($status ?? 0),
      ),
    };
  }
}
