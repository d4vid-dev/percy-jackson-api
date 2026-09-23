import { FlatList, Pressable, StyleSheet, Text, View } from 'react-native';
import { CharacterPhoto } from '../components/CharacterPhoto';
import { RequestState } from '../components/RequestState';
import { useApiData } from '../hooks/useApiData';
import { personagensApi, photoUrl } from '../services/personagens';
import { colors } from '../theme';

export function CharacterListScreen({ onSelect }: { onSelect: (id: number) => void }) {
  const { data, loading, error, reload } = useApiData(personagensApi.list);
  return (
    <View style={styles.screen}>
      <View style={styles.header}>
        <Text style={styles.eyebrow}>PERCY JACKSON</Text>
        <Text style={styles.title} accessibilityRole="header">Personagens</Text>
        <Text style={styles.subtitle}>Conheça quem faz parte deste universo.</Text>
      </View>
      {data && data.length > 0 ? (
        <FlatList data={data} keyExtractor={(item) => String(item.id)}
          contentContainerStyle={styles.list} refreshing={loading} onRefresh={reload}
          ListHeaderComponent={<View style={styles.listHeader}>
            <Text style={styles.count}>{data.length} personagens</Text>
            {error && <View accessibilityLiveRegion="polite">
              <Text style={styles.error}>{error}</Text>
              <Pressable accessibilityRole="button" onPress={reload} style={styles.retry}><Text style={styles.retryText}>Tentar novamente</Text></Pressable>
            </View>}
          </View>}
          renderItem={({ item }) => (
            <Pressable accessibilityRole="button" accessibilityLabel={`Ver detalhes de ${item.nome}`}
              onPress={() => onSelect(item.id)} style={({ pressed }) => [styles.card, pressed && { opacity: 0.75 }]}>
              <CharacterPhoto uri={photoUrl(item)} name={item.nome} />
              <View style={styles.cardBody}>
                <Text style={styles.name}>{item.nome}</Text>
                <Text style={styles.race}>{item.raca || 'Raça não informada'}</Text>
                <Text style={styles.parent}>{item.parentesco_divino || 'Parentesco não informado'}</Text>
              </View>
              <Text style={styles.arrow} accessible={false}>›</Text>
            </Pressable>
          )} />
      ) : <RequestState loading={loading} error={error} onRetry={reload} />}
    </View>
  );
}

const styles = StyleSheet.create({
  screen: { flex: 1 },
  header: { backgroundColor: colors.ink, paddingHorizontal: 24, paddingTop: 28, paddingBottom: 30, gap: 9 },
  eyebrow: { color: '#DFBB78', fontSize: 12, letterSpacing: 3, fontWeight: '700' },
  title: { color: '#FFFFFF', fontSize: 34, fontWeight: '700' },
  subtitle: { color: '#CFDCDE', fontSize: 15, lineHeight: 22 },
  list: { padding: 18, paddingBottom: 32, gap: 12, width: '100%', maxWidth: 760, alignSelf: 'center' },
  listHeader: { gap: 12, paddingBottom: 2 },
  count: { color: colors.muted, fontSize: 13, fontWeight: '600' },
  card: { flexDirection: 'row', gap: 14, alignItems: 'center', backgroundColor: colors.surface, borderRadius: 18, padding: 12, borderWidth: 1, borderColor: colors.line },
  cardBody: { flex: 1, gap: 6 },
  name: { fontSize: 18, color: colors.ink, fontWeight: '700' },
  race: { fontSize: 13, color: colors.accent, fontWeight: '600' },
  parent: { color: colors.muted, fontSize: 13, lineHeight: 19 },
  arrow: { color: colors.muted, fontSize: 28 },
  error: { color: colors.error, lineHeight: 22 },
  retry: { paddingVertical: 12, alignSelf: 'flex-start' },
  retryText: { color: colors.ink, fontWeight: '700' },
});
