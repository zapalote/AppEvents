import React, { useEffect } from 'react';
import { HashRouter as Router, Routes, Route } from 'react-router-dom';
import { TwentyfourStats, ThirtyStats, MonthlyStats, ReferralStats, TopicsStats } from './components';

const App = () => {
   // APP_TITLE is defined in public/config.js
  const title = window.APP_TITLE;
  // process.env.VARIABLES are defined in the .env file 
  const version = `${(process.env.REACT_APP_NAME)} v${(process.env.REACT_APP_VERSION)}`

  useEffect(() => {
    // set the page title
    document.title = title;
  }, []);

  const AppInfo = () => {
    return (
      <div style = {{ float: 'right', fontSize: '0.8em', color: '#ccc', margin: '1em' }} > {version} </div>
    )
  }

  return (
    <Router>
      <div id="app" className="App">
      <h2>{title}</h2>
      <div>
        <Routes homebase={process.env.PUBLIC_URL}>
          <Route path="/" element={<TwentyfourStats />} />
          <Route path="d/:date" element={<TwentyfourStats />} />
          <Route path="30" element={<ThirtyStats />} />
          <Route path="30/:month" element={<ThirtyStats />} />
          <Route path="m" element={<MonthlyStats />} />
          <Route path="s" element={<TopicsStats />} />
          <Route path="ref" element={<ReferralStats />} />
        </Routes>
      </div>
      <AppInfo />
      </div>
    </Router>
  );
}

export default App;
