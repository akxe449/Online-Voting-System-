// App.js
import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

import CandidateListScreen from './screens/CandidateListScreen';
import VoteConfirmScreen from './screens/VoteConfirmScreen';
import ConfirmationCodeScreen from './screens/ConfirmationCodeScreen';
import ResultsScreen from './screens/ResultsScreen';

const Stack = createNativeStackNavigator();

export default function App() {
  return (
    <NavigationContainer>
      <Stack.Navigator initialRouteName="CandidateList">
        <Stack.Screen
          name="CandidateList"
          component={CandidateListScreen}
          options={{ title: 'Vote' }}
        />
        <Stack.Screen
          name="VoteConfirm"
          component={VoteConfirmScreen}
          options={{ title: 'Confirm your vote' }}
        />
        <Stack.Screen
          name="ConfirmationCode"
          component={ConfirmationCodeScreen}
          options={{ title: 'Vote recorded', headerBackVisible: false }}
        />
        <Stack.Screen
          name="Results"
          component={ResultsScreen}
          options={{ title: 'Results' }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
}
