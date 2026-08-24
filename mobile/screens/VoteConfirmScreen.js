// screens/VoteConfirmScreen.js
import React, { useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ActivityIndicator, Alert } from 'react-native';
import { castVote } from '../api';

export default function VoteConfirmScreen({ route, navigation }) {
  const { token, candidateId, candidateName } = route.params;
  const [submitting, setSubmitting] = useState(false);

  async function handleConfirm() {
    setSubmitting(true);
    try {
      const confirmationCode = await castVote(token, candidateId);
      navigation.replace('ConfirmationCode', { confirmationCode });
    } catch (e) {
      // e.g. "This token has already been used to cast a vote" (409)
      Alert.alert('Could not cast vote', e.message, [
        { text: 'OK', onPress: () => navigation.navigate('CandidateList') },
      ]);
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <View style={styles.container}>
      <Text style={styles.question}>You are voting for</Text>
      <Text style={styles.name}>{candidateName}</Text>
      <Text style={styles.question}>Confirm?</Text>

      {submitting ? (
        <ActivityIndicator size="large" style={{ marginTop: 24 }} />
      ) : (
        <View style={styles.buttonRow}>
          <TouchableOpacity style={[styles.button, styles.no]} onPress={() => navigation.goBack()}>
            <Text style={styles.buttonText}>No, go back</Text>
          </TouchableOpacity>
          <TouchableOpacity style={[styles.button, styles.yes]} onPress={handleConfirm}>
            <Text style={[styles.buttonText, styles.yesText]}>Yes, cast my vote</Text>
          </TouchableOpacity>
        </View>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 24, backgroundColor: '#fff' },
  question: { fontSize: 18, color: '#444' },
  name: { fontSize: 28, fontWeight: '700', marginVertical: 12 },
  buttonRow: { flexDirection: 'row', marginTop: 32, gap: 12 },
  button: { paddingVertical: 14, paddingHorizontal: 20, borderRadius: 10 },
  no: { backgroundColor: '#e0e0e0' },
  yes: { backgroundColor: '#2e7d32' },
  buttonText: { fontSize: 16, fontWeight: '600', color: '#000' },
  yesText: { color: '#fff' },
});
