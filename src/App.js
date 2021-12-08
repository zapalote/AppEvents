import React from 'react';
import { HashRouter as Router, Routes, Route } from 'react-router-dom';
import { TwentyfourStats, ThirtyStats, MonthlyStats, ReferralStats, TopicsStats } from './components';

const App = () => {
  const title = window.APP_TITLE;
  const version = `${(process.env.REACT_APP_NAME)} v${(process.env.REACT_APP_VERSION)}`
  const homeUri = (process.env.NODE_ENV === 'development') ? '/' : '/app-events';

  // const TwentyfourStats = () => (<h2>Works</h2>);

  const AppInfo = () => {
    return (
      <div style = {{ float: 'right', fontSize: '0.8em', color: '#ccc', margin: '1em' }} >
        {version} on {homeUri}
      </div >
    )
  }

  console.log();
  return (
    <Router>
      <div id="app" className="App">
      <h2>{title}</h2>
      <div>
        <Routes homebase={homeUri}>
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
