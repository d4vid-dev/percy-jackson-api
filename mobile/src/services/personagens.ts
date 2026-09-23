import { API_URL } from '../config/api.ts';
import type { Personagem } from '../types/personagem.ts';

export class ApiError extends Error {}

function parsePersonagem(value: unknown): Personagem {
  if (!value || typeof value !== 'object') throw new ApiError('O servidor retornou dados inválidos.');
  const item = value as Record<string, unknown>;
  const textFields = ['data_nascimento', 'poderes', 'raca', 'parentesco_divino', 'imagem', 'imagem_url'];
  if (
    !Number.isInteger(item.id) || (item.id as number) < 1 ||
    typeof item.nome !== 'string' || typeof item.descricao !== 'string' ||
    !(item.idade === null || (Number.isInteger(item.idade) && (item.idade as number) >= 0)) ||
    textFields.some((field) => item[field] !== null && typeof item[field] !== 'string')
  ) throw new ApiError('O servidor retornou dados inválidos.');
  return item as Personagem;
}

export function createPersonagensApi(baseUrl: string, fetcher: typeof fetch = fetch, timeoutMs = 15000) {
  const base = baseUrl.trim().replace(/\/+$/, '');

  async function get(path: string, signal?: AbortSignal): Promise<unknown> {
    if (!/^https?:\/\/[^/\s]+/i.test(base)) {
      throw new ApiError('A conexão com o servidor ainda não foi configurada.');
    }

    const controller = new AbortController();
    const abort = () => controller.abort();
    signal?.addEventListener('abort', abort, { once: true });
    if (signal?.aborted) controller.abort();
    const timer = setTimeout(abort, timeoutMs);

    try {
      const response = await fetcher(`${base}${path}`, {
        headers: { Accept: 'application/json' }, signal: controller.signal,
      });
      if (!response.ok) {
        throw new ApiError(response.status === 404
          ? 'Este personagem não foi encontrado.'
          : 'O servidor não conseguiu atender à solicitação. Tente novamente.');
      }
      try {
        return await response.json();
      } catch (error) {
        if (controller.signal.aborted) throw error;
        throw new ApiError('O servidor retornou uma resposta inválida.');
      }
    } catch (error) {
      if (signal?.aborted) throw error;
      if (controller.signal.aborted) throw new ApiError('O servidor demorou para responder. Tente novamente.');
      if (error instanceof ApiError) throw error;
      throw new ApiError('Não foi possível conectar à API. Verifique sua conexão e tente novamente.');
    } finally {
      clearTimeout(timer);
      signal?.removeEventListener('abort', abort);
    }
  }

  return {
    async list(signal?: AbortSignal): Promise<Personagem[]> {
      const data = await get('/personagens', signal);
      if (!Array.isArray(data)) throw new ApiError('O servidor retornou uma lista inválida.');
      return data.map(parsePersonagem);
    },
    async show(id: number, signal?: AbortSignal): Promise<Personagem> {
      return parsePersonagem(await get(`/personagens/${id}`, signal));
    },
  };
}

export function photoUrl(personagem: Personagem, baseUrl = API_URL): string | null {
  const value = personagem.imagem_url || (personagem.imagem ? `storage/${personagem.imagem}` : null);
  if (!value) return null;
  try {
    // Relative Storage paths are resolved against the backend, never the Expo server.
    const backend = `${baseUrl.replace(/\/+$/, '').replace(/\/api$/, '')}/`;
    const url = new URL(value, backend);
    return ['http:', 'https:'].includes(url.protocol) ? url.href : null;
  } catch {
    return null;
  }
}

export const personagensApi = createPersonagensApi(API_URL);
