import { useFocusEffect } from 'expo-router';
import { useCallback, useRef, useState } from 'react';

export function useApiData<T>(load: (signal: AbortSignal) => Promise<T>) {
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const active = useRef<AbortController | null>(null);

  const reload = useCallback(async () => {
    active.current?.abort();
    const controller = new AbortController();
    active.current = controller;
    setLoading(true);
    setError(null);
    try {
      const result = await load(controller.signal);
      if (!controller.signal.aborted) setData(result);
    } catch (cause) {
      if (!controller.signal.aborted) {
        setError(cause instanceof Error ? cause.message : 'Não foi possível carregar os dados.');
      }
    } finally {
      if (!controller.signal.aborted) setLoading(false);
    }
  }, [load]);

  useFocusEffect(useCallback(() => {
    void reload();
    return () => active.current?.abort();
  }, [reload]));

  return { data, loading, error, reload };
}
