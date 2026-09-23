# Percy Jackson — Expo

App React Native + Expo com TypeScript, separado do Laravel. A listagem consulta
`GET /api/personagens` e a tela de detalhes consulta `GET /api/personagens/{id}`.
Não há catálogo local ou fallback com personagens fictícios no aplicativo.

A navegação usa Expo Router, com rotas em `src/app/`: `/` para a listagem e
`/personagens/[id]` para os detalhes, incluindo histórico de navegação na web.

## Preparar o Laravel

Na raiz do repositório, com PHP 8.2+ e as dependências Composer instaladas:

```bash
php artisan migrate
php artisan storage:link
php artisan serve --host=0.0.0.0 --port=8000
```

Para popular um banco novo, execute `php artisan db:seed --class=PersonagemSeeder`.
Não é necessário recriar um banco que já contém os personagens.

No `.env` do **Laravel**, ajuste `APP_URL` para o endereço acessível pelo dispositivo,
por exemplo `http://192.168.1.100:8000`, e execute `php artisan config:clear` após
alterar a configuração. Esse endereço também é usado nas URLs públicas das fotos.

## Configurar e iniciar o app

Use Node.js 22.13+ (Node 24 recomendado para os testes) e um Expo Go compatível com
o SDK definido em `package.json`. Em outro terminal:

```bash
cd mobile
npm ci
```

Copie `.env.example` para `.env` (`Copy-Item .env.example .env` no PowerShell ou
`cp .env.example .env` no macOS/Linux). Defina:

```dotenv
EXPO_PUBLIC_API_URL=http://192.168.1.100:8000/api
```

Substitua o IP de exemplo pelo IP do computador que executa o Laravel. A URL deve
incluir `/api`. A configuração é lida somente por `src/config/api.ts`; todas as
requisições passam por `src/services/personagens.ts`.

| Ambiente | URL de exemplo |
| --- | --- |
| Celular físico na mesma rede Wi-Fi | `http://IP_DO_COMPUTADOR:8000/api` |
| Emulador Android padrão | `http://10.0.2.2:8000/api` |
| Simulador iOS no mesmo Mac | `http://localhost:8000/api` |
| Navegador no computador da API | `http://localhost:8000/api` |

```bash
npm start
```

Abra o QR code no Expo Go. Para emuladores, use `npm run android` ou `npm run ios`
(o simulador iOS exige macOS). Para navegador, use `npm run web`.
Reinicie o Expo/recarregue o app após alterar `.env`. Variáveis `EXPO_PUBLIC_*` são
públicas no bundle: use-as para a URL, nunca para segredos.

No celular, `localhost` aponta para o próprio celular. Verifique se ele consegue
abrir `http://IP_DO_COMPUTADOR:8000/api/personagens` no navegador e se a porta 8000
está acessível na rede local. Um túnel do Expo expõe o Metro, não expõe o Laravel.
Para builds distribuídos, use uma API e imagens em HTTPS; os exemplos HTTP são
para desenvolvimento local com Expo Go.

## Telas e comportamento

- Listagem com foto, nome, raça e parentesco divino, alimentada pela API.
- Toque no personagem para consultar detalhes, descrição, poderes, idade e nascimento.
- Volte pelo botão da tela ou pelo botão Voltar do Android.
- Arraste para baixo para atualizar os dados; falhas oferecem nova tentativa.
- Campos desconhecidos são apresentados como não informados, preservando `null`.
- Fotos ausentes ou indisponíveis mostram uma indicação de ausência, sem inventar fotos.
- Consultas têm timeout e são canceladas ao sair da tela. Erros de atualização
  preservam os últimos dados carregados.

## CORS e fotos

Não foram necessárias mudanças no backend: o Laravel 12 já inclui `HandleCors`
e a configuração padrão permite requisições a `api/*`, sem credenciais. O app não
usa cookies nem autenticação. CORS aplica-se à versão web; apps nativos não têm
a política de CORS dos navegadores.

Se o ambiente de implantação tiver uma configuração própria restritiva, permita
a origem do Expo Web (normalmente `http://localhost:8081`) em `config/cors.php`.
Não é necessário adicionar middleware duplicado nem alterar as rotas do CRUD.

O app usa `imagem_url` retornada pela API. Se estiver ausente/nula e houver
`imagem`, resolve o caminho em `/storage/` no backend. Para fotos que não carregam,
confira `APP_URL`, `php artisan storage:link` e o acesso à URL pelo dispositivo.

## Verificação

```bash
npm run typecheck
npm run lint
npm test
npx expo export --platform all
```

Os testes usam respostas simuladas apenas em `tests/`, verificando endpoints,
contrato de dados, erros, timeout, cancelamento e URLs de fotos. Para validar a
integração real, inicie o Laravel e confira listagem, detalhes, foto, retorno à
lista e nova tentativa após interromper/restabelecer a API.

Referências: [variáveis de ambiente do Expo](https://docs.expo.dev/guides/environment-variables/),
[rede no React Native](https://reactnative.dev/docs/network),
[CORS padrão do Laravel 12](https://github.com/laravel/framework/blob/12.x/config/cors.php).
