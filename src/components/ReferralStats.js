import React, { useEffect, useState } from "react";
import { LoadingIndicator, StatsTable } from '.';
import { getApi } from '../utils';
import { useNavigate } from 'react-router-dom';

const ReferralStats = () => {
  const navigate = useNavigate();

  const [loading, setLoading] = useState(false);
  const [results, setResults] = useState([]);

  useEffect(() => {
    let mounted = true;
    setLoading(true);
    // include a delay to ensure previous db updates have completed
    setTimeout(() => {
      getApi({ url: '?ref' })
        .then(data => {
          if (mounted) {
            setResults(data);
          }
        })
        .finally(() => {
          setLoading(false);
        });
    }, 200);
    return () => { mounted = false; }
  }, []);

  const handleDone = () => {
    navigate('/');
  }

  const NoResults = () => {
    return (
      <>
        <h3>No Results Available
          <div className="btns">
            <div onClick={handleDone} className="button" >Done</div>
          </div>
        </h3>
      </>
    );
  }

  const ListResults = () => {

    let table = [];
    let all = [];
    let bots = [];
    results.referrals && results.referrals.forEach((ref, idx) => {
      table.push([ref, results.landing[idx], results.times[idx].substr(11)]);
    });
    for (const [k, v] of Object.entries(results.all)) {
      all.push([k, v]);
    }
    results.bots.ref?.forEach((ref, idx) => {
      bots.push([ref, results.bots.land[idx], results.bots.upd[idx].substr(11)]);
    });

    return (
      <>
        { table && (
          <>
            <h3>Referrals {results.last}
              <div className="btns">
                <div onClick={handleDone} className="button" >Done</div>
              </div>
            </h3>
            <StatsTable headings={['Referral', 'Landing', 'Time']} data={table} />
          </>
        )}
        {bots && (
          <>
            <h3>Robots (last 24hrs)
              <div className="btns">
                <div onClick={handleDone} className="button" >Done</div>
              </div>
            </h3>
            <StatsTable headings={['Robots', 'Landing', 'Time']} data={bots} />
          </>
        )}
        <h3>All referrals 
          <div className="btns">
            <div onClick={handleDone} className="button" >Done</div>
          </div>
        </h3>
        <StatsTable headings={['Referral', 'Events']} data={all} />
      </>
    );
  }

  return (
    <div>
      {loading ?
        <LoadingIndicator coverall={false} fill='#6b966b' /> :
        (!results || typeof results.all === 'undefined' ? <NoResults /> : <ListResults />)
      }
    </div>
  );
}

export default ReferralStats;