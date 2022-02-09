import React, { useEffect, useState } from "react";
import { LoadingIndicator, SimpleChart, StatsTable, IPpopUp, Nav } from '.';
import { getApi, anonIP } from '../utils';
import { useNavigate, useParams } from 'react-router-dom';

const TwentyfourStats = () => {
  const params = useParams();
  const date = params.date ?? 'last 24 hrs';
  const navigate =  useNavigate();

  const [loading, setLoading] = useState(false);
  const [showPopup, setShowPopup] = useState(false);
  const [results, setResults] = useState([]);
  const [ipEvents, setIpEvents] = useState([]);

  useEffect(() => {
    let mounted = true;
    const query = (params.date)? '?d='+params.date : '';
    setLoading(true);
    getApi({ url: query })
    .then(data => {
      if(mounted){
        if(typeof data.ips === 'undefined'){
          navigate("/30");
        }
        setResults(data);
      }
    })
    .finally(() => {
      setLoading(false);
      setShowPopup(false);
    });
    return () => { mounted = false; }
  }, [params]);

  const initChart = (values, heading) => {
    let labels = [];
    let chart = [];
    let tooltips = [];
    let chartCfg = {};
    if (!values) return { chartCfg, chart, labels, tooltips };

    for( const [k, v] of Object.entries(values)){
      labels.push(k.substring(1));
      chart.push(v);
      tooltips.push(k.substring(1));
    }
    let wh = '300px';
    let ctype = 'column';
    if(window.innerWidth <= 480) {
        ctype = 'bar';
        wh = '100%';
    }

    chartCfg = {
        title: { text: `App events ${heading}`, align: 'left' },
        type: ctype,
        layout: { width: '100%', height: wh },
        item: { 
            color: ['#0f99d6'], 
            labelInterval: 1,
            render: { margin: 0.2, size: 'relative' }
        }
    };
    return { chartCfg, chart, labels, tooltips };
  }

  const onPopUP = (ip) => {
    getApi({ url: '?pip='+window.btoa(ip) })
      .then(data => {
          setIpEvents(data);
          setShowPopup(true);
        });
  }

  const onClosePopup = () => {
    setShowPopup(false);
  }

  const ListResults = () => {
    const { chartCfg, chart, labels, tooltips } = initChart(results.chart, results.last);

    let popupLinks = [];
    let table = [];
    let rowsep = [];
    results.ips?.forEach((ip, idx) => {
      table.push([anonIP(ip), results.hits[idx], results.times[idx].substr(11)]);
      popupLinks.push(ip);
      rowsep.push(results.rowsep[idx]);
    });

    return (
      <>
        <div className='chart-container'>
          <SimpleChart options={chartCfg} values={chart} labels={labels} tooltips={tooltips} />
        </div>

        <h3>Sessions — {date}
          <Nav active={params.date ?? '24 hrs'} />
        </h3>

        <StatsTable headings={['Session', 'Hits', 'Last']} data={table} rowsep={rowsep}
          popupLinks={popupLinks} onPopUp={onPopUP} />
        {showPopup && <IPpopUp onClosePopup={onClosePopup} data={ipEvents} show={showPopup} />}
      </>
    );
  }

  return (
    <div>
      { loading?
        <LoadingIndicator coverall={false} fill='#6b966b' /> :
        (results && typeof results.ips != 'undefined' && <ListResults />)
      }
    </div>
  );
}

export default TwentyfourStats;