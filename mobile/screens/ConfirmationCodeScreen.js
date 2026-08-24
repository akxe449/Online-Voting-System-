// screens/ConfirmationCodeScreen.js
import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';

export default function ConfirmationCodeScreen({ route, navigation }) {
  const { confirmationCode } = route.params;

  return (
    <View style={styles.container}>
      <Text style={styles.checkmark}>✓</Text>
      <Text style={styles.title}>Your vote was recorded</Text>
      <Text style={styles.label}>Confirmation code — save this:</Text>
      <View style={styles.codeBox}>
        <Text style={styles.code}>{confirmationCode}</Text>
      </View>
      <Text style={styles.helperText}>
        You can use this code later to verify your vote was counted, without
        revealing who you voted for.
      </Text>
      <TouchableOpacity
        style={styles.button}
        onPress={() => navigation.navigate('Results')}
      >
        <Text style={styles.buttonText}>View Results</Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 24, backgroundColor: '#fff' },
  checkmark: { fontSize: 48, color: '#2e7d32', marginBottom: 8 },
  title: { fontSize: 22, fontWeight: '700', marginBottom: 24 },
  label: { fontSize: 14, color: '#666', marginBottom: 8 },
  codeBox: {
    borderWidth: 2,
    borderColor: '#2e7d32',
    borderRadius: 12,
    paddingVertical: 16,
    paddingHorizontal: 24,
    marginBottom: 20,
  },
  code: { fontSize: 32, fontWeight: '800', letterSpacing: 2 },
  helperText: { fontSize: 13, color: '#888', textAlign: 'center', marginBottom: 32 },
  button: { backgroundColor: '#000', paddingVertical: 14, paddingHorizontal: 28, borderRadius: 10 },
  buttonText: { color: '#fff', fontSize: 16, fontWeight: '600' },
});
