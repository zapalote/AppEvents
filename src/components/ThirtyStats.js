import React, { useEffect, useState } from "react";
import { LoadingIndicator } from '.';
import { getApi, SimpleChart, StatsTable } from '../utils';
import { useNavigate, useParams } from 'react-router-dom';

const ThirtyStats = () => {
  const navigate = useNavigate();
  const params = useParams();
  const period = params.month ?? 'last 30 days';
  const apiQuery = (params.month)? `?md=${params.month}` : '?30'; 

  const [loading, setLoading] = useState(false);
  const [results, setResults] = useState([]);

  useEffect(() => {
    let mounted = true;
    setLoading(true);
    // include a delay to ensure previous db updates have completed
    setTimeout(() => {
      getApi({ url: apiQuery })
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

  const initChart = (values) => {
    let labels = [];
    let chart = [];
    let tooltips = [];
    let chartCfg = {};
    if (!values) return { chartCfg, chart, labels, tooltips };

    for (const [k, v] of Object.entries(values)) {
      labels.push(k);
      chart.push(v);
      tooltips.push(k);
    }
    let wh = '300px';
    let ctype = 'column';
    if (window.innerWidth <= 480) {
      ctype = 'bar';
      wh = '100%';
    }

    chartCfg = {
      title: { text: 'App Sessions '+period, align: 'left' },
      type: ctype,
      layout: { width: '100%', height: wh },
      item: {
        color: ['#0f99d6'],
        labelInterval: 5,
        render: { margin: 0.2, size: 'relative' }
      }
    };
    return { chartCfg, chart, labels, tooltips };
  }

  const handleDone = () => {
    navigate('/');
  }

  const NoResults = () => {
    return (
      <div>
        <div>
          <button type="button" onClick={handleDone} className="button" >Done</button>
        </div>
        <h3>No Results Available</h3>
      </div>
    );
  }

  const ListResults = () => {
    const { chartCfg, chart, labels, tooltips } = initChart(results.chart);

    let table = [];
    let drill = [];
    results.date.forEach((d, idx) => {
      drill.push('/d/'+d);
      const date = new Date(d).toString().split(' ');
      table.push([`${date[0]}, ${date[2]} ${date[1]}`, results.sessions[idx], results.hits[idx]]);
    });

    return (
      <>
        <div className='chart-container'>
          <SimpleChart options={chartCfg} values={chart} labels={labels} tooltips={tooltips} />
        </div>

        <h3>Sessions — {period}
          <div className="btns">
            <div onClick={() => navigate('/m')} className="button" >monthly</div>
            <div onClick={() => navigate('/ref')} className="button" >referrals</div>
            <div onClick={() => navigate('/s')} className="button" >topics</div>
            <div onClick={handleDone} className="button" >Done</div>
          </div>
        </h3>

        <StatsTable headings={['Date', 'Sessions', 'Hits']} data={table} drill={drill} />
      </>
    );
  }

  return (
    <div>
      {loading ?
        <LoadingIndicator coverall={false} fill='#6b966b' /> :
        (!results || typeof results.date === 'undefined' ? <NoResults /> : <ListResults />)
      }
    </div>
  );
}

export default ThirtyStats;