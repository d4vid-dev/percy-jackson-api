import { router } from 'expo-router';
import { CharacterListScreen } from '../screens/CharacterListScreen';

export default function Index() {
  return <CharacterListScreen onSelect={(id) => router.push({ pathname: '/personagens/[id]', params: { id } })} />;
}
