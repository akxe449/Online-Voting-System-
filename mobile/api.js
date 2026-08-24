// api.js
//
// IMPORTANT: "localhost" here means the phone/simulator itself, not your
// Mac. Replace BASE_URL below with your Mac's actual LAN IP address.
// Find it by running this in Terminal:  ipconfig getifaddr en0
// Then use:  http://<that-ip>/voting-system/api

export const BASE_URL = 'http://192.168.29.163/voting-system/api'; // <-- CHANGE THIS

export async function getCandidates(electionId) {
  const res = await fetch(`${BASE_URL}/candidates.php?election_id=${electionId}`);
  if (!res.ok) throw new Error('Failed to load candidates');
  return res.json();
}

export async function getDevToken(electionId) {
  const res = await fetch(`${BASE_URL}/issue-token.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ election_id: electionId }),
  });
  if (!res.ok) throw new Error('Failed to get a dev token');
  const data = await res.json();
  return data.token_hash;
}

export async function castVote(token, candidateId) {
  const res = await fetch(`${BASE_URL}/cast-vote.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ token, candidate_id: candidateId }),
  });
  const data = await res.json();
  if (!res.ok) {
    // cast-vote.php returns a JSON error body even on failure (404/409/500) —
    // surface that message rather than a generic "something went wrong"
    throw new Error(data.error || 'Vote could not be cast');
  }
  return data.confirmation_code;
}

export async function getResults(electionId) {
  const res = await fetch(`${BASE_URL}/results.php?election_id=${electionId}`);
  if (!res.ok) throw new Error('Failed to load results');
  return res.json();
}
