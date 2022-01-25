import React, { useEffect, useState } from "react";
import { LoadingIndicator, SimpleChart, StatsTable, Nav } from '.';
import { getApi } from '../utils';
import { useNavigate } from 'react-router-dom';

const MonthlyStats = () => {
  const navigate = useNavigate();

  const [loading, setLoading] = useState(false);
  const [results, setResults] = useState([]);

  useEffect(() => {
    let mounted = true;
    setLoading(true);
    // include a delay to ensure previous db updates have completed
    setTimeout(() => {
      getApi({ url: '?m' })
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
      title: { text: 'Monthly App Events', align: 'left' },
      type: ctype,
      layout: { width: '100%', height: wh },
      item: {
        color: ['#0f99d6'],
        labelInterval: 2,
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
      <>
        <h3>No Results Available</h3>
        <div onClick={handleDone} className="button" >Done</div> 
      </>
    );
  }

  const ListResults = () => {
    const { chartCfg, chart, labels, tooltips } = initChart(results.chart);

    let table = [];
    let drill = [];
    results.months?.forEach((m, idx) => {
      drill.push('/30/'+m);
      table.push([m, results.sessions[idx], results.hits[idx]]);
    });
    const month = new Date().toString().split(' ')[1];

    return (
      <>
        <div className='chart-container'>
          <SimpleChart options={chartCfg} values={chart} labels={labels} tooltips={tooltips} />
        </div>

        <h3>Forecast {month}: {results.forecast} events
          <Nav active="monthly" />
        </h3>

        <StatsTable headings={['Month', 'Sessions', 'Hits']} data={table} drill={drill} />
      </>
    );
  }

  return (
    <div>
      {loading ?
        <LoadingIndicator coverall={false} fill='#6b966b' /> :
        ((!results || typeof results.months === 'undefined') ? <NoResults /> : <ListResults />)
      }
    </div>
  );
}

export default MonthlyStats;