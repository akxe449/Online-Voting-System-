// screens/ResultsScreen.js
import React, { useEffect, useState } from 'react';
import { View, Text, FlatList, ActivityIndicator, StyleSheet } from 'react-native';
import { getResults } from '../api';

const ELECTION_ID = 1;

export default function ResultsScreen() {
  const [results, setResults] = useState(null);
  const [error, setError] = useState(null);

  useEffect(() => {
    getResults(ELECTION_ID)
      .then(setResults)
      .catch((e) => setError(e.message));
  }, []);

  if (error) {
    return (
      <View style={styles.center}>
        <Text style={styles.errorText}>Couldn't load results: {error}</Text>
      </View>
    );
  }

  if (!results) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" />
      </View>
    );
  }

  const tallyEntries = results.tally; // already an array: [{ candidate_id, name, votes, percentage }, ...]

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Results</Text>
      <Text style={styles.subtitle}>Total ballots cast: {results.total_ballots}</Text>
      <FlatList
        data={tallyEntries}
        keyExtractor={(item) => String(item.candidate_id)}
        renderItem={({ item }) => (
          <View style={styles.row}>
            <Text style={styles.rowLabel}>{item.name}</Text>
            <Text style={styles.rowCount}>{item.votes} ({item.percentage}%)</Text>
          </View>
        )}
        ListEmptyComponent={<Text style={styles.helperText}>No votes cast yet.</Text>}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, padding: 20, paddingTop: 60, backgroundColor: '#fff' },
  center: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 20 },
  title: { fontSize: 22, fontWeight: '700' },
  subtitle: { fontSize: 14, color: '#666', marginBottom: 16 },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  rowLabel: { fontSize: 16 },
  rowCount: { fontSize: 16, fontWeight: '700' },
  helperText: { color: '#888', textAlign: 'center', marginTop: 20 },
  errorText: { color: '#c0392b', fontSize: 16, textAlign: 'center' },
});
