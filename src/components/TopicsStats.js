import React, { useEffect, useState } from "react";
import { LoadingIndicator, StatsTable, Nav } from '.';
import { getApi } from '../utils';
import { useNavigate } from 'react-router-dom';

const TopicsStats = () => {
  const navigate = useNavigate();

  const [loading, setLoading] = useState(false);
  const [results, setResults] = useState([]);

  useEffect(() => {
    let mounted = true;
    setLoading(true);
    // include a delay to ensure previous db updates have completed
    setTimeout(() => {
      getApi({ url: '?s' })
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
    for (const [k, v] of Object.entries(results.top30days)) {
      table.push([k, v]);
    }
    for (const [k, v] of Object.entries(results.topall)) {
      all.push([k, v]);
    }

    return (
      <>
        {table && (
          <>
            <h3>Top topics last 30 days {table.length < 300? '' : '(top 300)'}
              <Nav active='topics' />
            </h3>
            <StatsTable headings={['Topic', 'Hits']} data={table} />
          </>
        )}
        <h3>Topic ranking {table.length < 300 ? '' : '(top 300)'}
          <div className="btns">
            <div onClick={handleDone} className="button" >Done</div>
          </div>
        </h3>
        <StatsTable headings={['Topic', 'Hits']} data={all} />
      </>
    );
  }

  return (
    <div>
      {loading ?
        <LoadingIndicator coverall={false} fill='#6b966b' /> :
        (!results || typeof results.topall === 'undefined' ? <NoResults /> : <ListResults />)
      }
    </div>
  );
}

export default TopicsStats;