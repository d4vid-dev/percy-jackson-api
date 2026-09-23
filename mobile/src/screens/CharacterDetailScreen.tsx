import { useCallback } from 'react';
import { Pressable, RefreshControl, ScrollView, StyleSheet, Text, View } from 'react-native';
import { CharacterPhoto } from '../components/CharacterPhoto';
import { RequestState } from '../components/RequestState';
import { useApiData } from '../hooks/useApiData';
import { personagensApi, photoUrl } from '../services/personagens';
import { colors } from '../theme';

function Field({ label, value }: { label: string; value: string | null }) {
  return <View style={styles.field}><Text style={styles.label}>{label}</Text><Text style={styles.value}>{value || 'Não informado'}</Text></View>;
}

export function CharacterDetailScreen({ id, onBack }: { id: number; onBack: () => void }) {
  const load = useCallback((signal: AbortSignal) => personagensApi.show(id, signal), [id]);
  const { data, loading, error, reload } = useApiData(load);
  return <View style={styles.screen}>
    <View style={styles.topBar}>
      <Pressable accessibilityRole="button" accessibilityLabel="Voltar para personagens" onPress={onBack} style={styles.back}>
        <Text style={styles.backText}>‹  Personagens</Text>
      </Pressable>
    </View>
    {data ? <ScrollView contentContainerStyle={styles.content}
      refreshControl={<RefreshControl refreshing={loading} onRefresh={reload} tintColor={colors.ink} />}>
      {error && <RequestState error={error} onRetry={reload} />}
      <CharacterPhoto uri={photoUrl(data)} name={data.nome} large />
      <Text style={styles.race}>{data.raca || 'Raça não informada'}</Text>
      <Text style={styles.title} accessibilityRole="header">{data.nome}</Text>
      <Text style={styles.description}>{data.descricao}</Text>
      <View style={styles.fields}>
        <Field label="Parentesco divino" value={data.parentesco_divino} />
        <Field label="Idade" value={data.idade === null ? null : `${data.idade} anos`} />
        <Field label="Nascimento" value={data.data_nascimento?.split('-').reverse().join('/') ?? null} />
        <Field label="Poderes" value={data.poderes} />
      </View>
    </ScrollView> : <RequestState loading={loading} error={error} onRetry={reload} />}
  </View>;
}

const styles = StyleSheet.create({
  screen: { flex: 1 },
  topBar: { backgroundColor: colors.ink, paddingHorizontal: 12 },
  back: { padding: 14, alignSelf: 'flex-start' },
  backText: { color: '#FFFFFF', fontSize: 16, fontWeight: '600' },
  content: { padding: 22, paddingBottom: 40, gap: 16, width: '100%', maxWidth: 760, alignSelf: 'center' },
  race: { color: colors.accent, fontSize: 14, fontWeight: '700', marginTop: 8 },
  title: { color: colors.ink, fontSize: 32, fontWeight: '700' },
  description: { fontSize: 16, color: colors.muted, lineHeight: 26 },
  fields: { backgroundColor: colors.surface, borderRadius: 18, paddingHorizontal: 20, borderWidth: 1, borderColor: colors.line },
  field: { paddingVertical: 18, gap: 6 },
  label: { color: colors.accent, fontSize: 12, fontWeight: '700', textTransform: 'uppercase', letterSpacing: 1 },
  value: { fontSize: 16, lineHeight: 24, color: colors.ink },
});
