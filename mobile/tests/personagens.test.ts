import assert from 'node:assert/strict';
import { test } from 'node:test';
import { createPersonagensApi, photoUrl } from '../src/services/personagens.ts';
import type { Personagem } from '../src/types/personagem.ts';

// Synthetic fixture used only by tests; the app has no local character catalogue.
const character: Personagem = {
  id: 7, nome: 'Personagem de teste', descricao: 'Registro de teste',
  idade: null, data_nascimento: null, raca: null, parentesco_divino: null,
  poderes: null, imagem: null, imagem_url: null,
};

test('list uses the configured API, normalizes trailing slashes and accepts unknown fields', async () => {
  const fetcher: typeof fetch = async (url, options) => {
    assert.equal(url, 'http://api.test/api/personagens');
    assert.deepEqual(options?.headers, { Accept: 'application/json' });
    assert.ok(options?.signal);
    return Response.json([character]);
  };
  const api = createPersonagensApi('http://api.test/api///', fetcher);
  assert.deepEqual(await api.list(), [character]);
});

test('details fetch their own endpoint by ID', async () => {
  const api = createPersonagensApi('http://api.test/api', async (url) => {
    assert.equal(url, 'http://api.test/api/personagens/7');
    return Response.json({ ...character, idade: 0, data_nascimento: '2000-02-29' });
  });
  assert.equal((await api.show(7)).idade, 0);
});

test('an empty API list stays empty', async () => {
  const api = createPersonagensApi('http://api.test/api', async () => Response.json([]));
  assert.deepEqual(await api.list(), []);
});

test('404, server errors and network errors have readable messages', async () => {
  for (const [status, expected] of [[404, /não foi encontrado/], [500, /servidor não conseguiu/]] as const) {
    const api = createPersonagensApi('http://api.test/api', async () => new Response(null, { status }));
    await assert.rejects(api.show(7), expected);
  }
  const api = createPersonagensApi('http://api.test/api', async () => { throw new TypeError('Failed to fetch'); });
  await assert.rejects(api.list(), /Não foi possível conectar/);
});

test('invalid JSON and invalid character contracts are rejected', async () => {
  const html = createPersonagensApi('http://api.test/api', async () => new Response('<html>Error</html>'));
  await assert.rejects(html.list(), /resposta inválida/);
  for (const payload of [{ data: [character] }, [{ ...character, idade: 'Imortal' }], [null]]) {
    const api = createPersonagensApi('http://api.test/api', async () => Response.json(payload));
    await assert.rejects(api.list(), /inválid/);
  }
});

test('missing configuration does not make a request', async () => {
  const api = createPersonagensApi('', async () => { assert.fail('fetch should not run'); });
  await assert.rejects(api.list(), /não foi configurada/);
});

test('slow requests time out and navigation cancellation reaches fetch', async () => {
  const waitingFetch: typeof fetch = (_url, options) => new Promise((_resolve, reject) => {
    const onAbort = () => reject(new DOMException('Aborted', 'AbortError'));
    if (options?.signal?.aborted) onAbort();
    else options?.signal?.addEventListener('abort', onAbort, { once: true });
  });
  const api = createPersonagensApi('http://api.test/api', waitingFetch, 10);
  await assert.rejects(api.list(), /demorou para responder/);
  const controller = new AbortController();
  const result = api.list(controller.signal);
  controller.abort();
  await assert.rejects(result, { name: 'AbortError' });
});

test('photo URLs use the backend, preserve absolute URLs and allow no photo', () => {
  const base = 'http://192.168.1.100:8000/api';
  assert.equal(photoUrl(character, base), null);
  assert.equal(photoUrl({ ...character, imagem: 'personagens/foto.jpg' }, base),
    'http://192.168.1.100:8000/storage/personagens/foto.jpg');
  assert.equal(photoUrl({ ...character, imagem_url: '/storage/personagens/foto.jpg' }, base),
    'http://192.168.1.100:8000/storage/personagens/foto.jpg');
  assert.equal(photoUrl({ ...character, imagem_url: 'https://photos.test/foto.jpg' }, base),
    'https://photos.test/foto.jpg');
  assert.equal(photoUrl({ ...character, imagem_url: 'javascript:alert(1)' }, base), null);
});
