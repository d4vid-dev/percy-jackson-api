import { useState } from 'react';
import { Image, StyleSheet, Text, View } from 'react-native';
import { colors } from '../theme';

export function CharacterPhoto({ uri, name, large = false }: { uri: string | null; name: string; large?: boolean }) {
  const [failedUri, setFailedUri] = useState<string | null>(null);

  return (
    <View style={[styles.frame, large && styles.large]}>
      {uri && uri !== failedUri ? (
        <Image source={{ uri }} style={styles.image} resizeMode="cover"
          accessibilityLabel={`Foto de ${name}`} onError={() => setFailedUri(uri)} />
      ) : (
        <View style={styles.placeholder} accessibilityLabel={`${name}, sem foto disponível`}>
          <Text style={[styles.initial, large && styles.largeInitial]}>{name.charAt(0).toUpperCase()}</Text>
          <Text style={styles.caption}>Sem foto</Text>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  frame: { width: 88, height: 108, borderRadius: 12, overflow: 'hidden', backgroundColor: colors.placeholder },
  large: { width: '100%', height: 300, borderRadius: 20 },
  image: { width: '100%', height: '100%' },
  placeholder: { flex: 1, justifyContent: 'center', alignItems: 'center', gap: 4 },
  initial: { fontSize: 32, fontWeight: '600', color: colors.muted },
  largeInitial: { fontSize: 72 },
  caption: { fontSize: 12, color: colors.muted },
});
