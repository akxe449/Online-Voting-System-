// screens/CandidateListScreen.js
//
// DEV-ONLY NOTE: Since Person A's registration/OTP/time-slot flow doesn't
// exist yet, this screen fetches its own token on load via the Day-1 test
// endpoint (issue-token.php), so you can test your entire voting flow
// standalone. Once A's screens exist, this screen should instead RECEIVE
// its token as a navigation param handed off from their time-slot screen —
// delete the getDevToken() call at that point.

import React, { useEffect, useState } from 'react';
import { View, Text, FlatList, TouchableOpacity, ActivityIndicator, StyleSheet } from 'react-native';
import { getCandidates, getDevToken } from '../api';

const ELECTION_ID = 1; // matches the seeded demo election

export default function CandidateListScreen({ navigation }) {
  const [candidates, setCandidates] = useState([]);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    async function load() {
      try {
        const [candidateList, devToken] = await Promise.all([
          getCandidates(ELECTION_ID),
          getDevToken(ELECTION_ID),
        ]);
        setCandidates(candidateList);
        setToken(devToken);
      } catch (e) {
        setError(e.message);
      } finally {
        setLoading(false);
      }
    }
    load();
  }, []);

  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" />
        <Text style={styles.helperText}>Loading candidates…</Text>
      </View>
    );
  }

  if (error) {
    return (
      <View style={styles.center}>
        <Text style={styles.errorText}>Couldn't load candidates: {error}</Text>
        <Text style={styles.helperText}>
          Check BASE_URL in api.js and confirm XAMPP is running.
        </Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Choose a candidate</Text>
      <FlatList
        data={candidates}
        keyExtractor={(item) => String(item.candidate_id)}
        renderItem={({ item }) => (
          <TouchableOpacity
            style={styles.card}
            onPress={() =>
              navigation.navigate('VoteConfirm', {
                token,
                candidateId: item.candidate_id,
                candidateName: item.name,
              })
            }
          >
            <Text style={styles.cardText}>{item.name}</Text>
          </TouchableOpacity>
        )}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, padding: 20, paddingTop: 60, backgroundColor: '#fff' },
  center: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 20 },
  title: { fontSize: 22, fontWeight: '700', marginBottom: 16 },
  card: {
    padding: 18,
    borderRadius: 10,
    backgroundColor: '#f2f2f2',
    marginBottom: 12,
  },
  cardText: { fontSize: 17, fontWeight: '500' },
  helperText: { marginTop: 10, color: '#666', textAlign: 'center' },
  errorText: { color: '#c0392b', fontSize: 16, textAlign: 'center', marginBottom: 8 },
});
