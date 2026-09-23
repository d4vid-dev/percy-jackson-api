import { router, useLocalSearchParams } from 'expo-router';
import { RequestState } from '../../components/RequestState';
import { CharacterDetailScreen } from '../../screens/CharacterDetailScreen';

export default function Detail() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const characterId = Number(id);
  const back = () => router.canGoBack() ? router.back() : router.replace('/');

  if (!Number.isSafeInteger(characterId) || characterId < 1) {
    return <RequestState error="Este personagem não foi encontrado." onRetry={back} />;
  }

  return <CharacterDetailScreen key={characterId} id={characterId} onBack={back} />;
}
