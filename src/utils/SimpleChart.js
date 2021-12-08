import React, { useState, useEffect } from "react";
import './simple-chart.css';

// inspired on Andy Gongea's simple-chart Jquery plugin
// https://github.com/andygongea/simple-chart/blob/master/simple-chart.js
const SimpleChart = (props) => {

  const { options, values, labels, tooltips } = props;

  const [config, setConfig] = useState({
      title: {
        text: 'Simple Chart',
        align: 'left'
      },
      type: 'column', /* bar, column */
      layout: {
        width: '100%', /* String value: in px or percentage */
        height: '300px' /* String value: in px or percentage */
      },
      item: {
        color: ['#333'],
        decimals: 2,
        height: null,
        labelInterval: 1,
        render: {
          size: 'relative', /* Relative - the height of the items is relative to the maximum value */
          margin: 0,
          radius: null
        }
      },
      marker: null
  });

  const [chartClass, setChartClass] = useState(null);
  const [barMargin, setBarMargin] = useState(null);
  const [labelInterval, setLabelInterval] = useState(null);

  useEffect(() => {
    const cfg = Object.assign(config, options);
    setConfig(cfg);

    setChartClass('sc-' + cfg.type);
    setBarMargin((cfg.item.render.margin >= 0) ? cfg.item.render.margin : 0.5);
    setLabelInterval(cfg.item.labelInterval);
  }, [options]);

  function recordsClass() {
    var rangesClass = '';
    const n = values.length;
    if ((n > 0) && (n <= 10)) {
      rangesClass = 'sc-10-items';
    } else if ((n > 10) && (n <= 20)) {
      rangesClass = 'sc-20-items';
    } else if (n > 20) {
      rangesClass = 'sc-many-items';
    }

    return rangesClass;
  }

  function setItemSize() {
    let itemSize = [], nominator = 1;
    const maxValue = Math.max.apply(null, values);
    const total = values.reduce((a, b) => a + b, 0);

    if (config.item.render.size === 'absolute') {
      nominator = total;
    } else {
      nominator = maxValue;
    }

    for (let v of values) {
      itemSize.push(v * 100 / nominator);
    }
    return itemSize;
  }

  function setBarColor() {
    let barColor = [];

    if (config.item.color.length === 1) {
      barColor = new Array(values.length).fill(config.item.color[0]);
    } else {
      barColor = [...config.item.color];
    }
    return barColor;
  }

  const BarChart = () => {
    const itemWidth = setItemSize();
    const barColor = setBarColor();
    const classes = `sc-chart ${chartClass} ` + recordsClass();
    return (
      <div className={classes} style={{
          width: config.layout.width, 
          height: config.layout.height
        }}>
        <div className="sc-title" style={{
          textAlign: config.title.align
        }}>{config.title.text}</div>
        <div className="sc-canvas">
          {values.map((item, pos) => {
            return(
                <div key={`bar${pos}`} className="sc-item" style={{
                  width: itemWidth[pos] + '%',
                  backgroudColor: barColor[pos],
                  zIndex: (values.length - pos)
                }} >
                  <span className="sc-label">{labels[pos]}</span>
                  <span className="sc-value">{item}</span>
                  <div className="sc-tooltip">
                    <span className="sc-tooltip-value">{tooltips[pos]}</span>
                  </div>
                </div>
              )
            })
          }
        </div>
      </div>
    )
  }

  const ColumnChart = () => {
    const itemHeight = setItemSize();
    const barColor = setBarColor();
    const classes = `sc-chart ${chartClass} `+recordsClass();
    const itemWidth = (100 - values.length * barMargin - barMargin) / values.length;
    return (
      <div className={classes} style={{
        width: config.layout.width,
        height: config.layout.height
      }}>
        <div className="sc-title" style={{
          textAlign: config.title.align
        }}>{config.title.text}</div>
        <div className="sc-canvas">
          {values.map((item, pos) => {
            return (
              <div key={`col${pos}`} className="sc-item" style={{
                left: ((itemWidth + barMargin) * pos + barMargin) + '%',
                width: itemWidth + '%',
                height: itemHeight[pos] + '%',
                backgroudColor: barColor[pos]
              }} >
                <span className="sc-label">{(pos % labelInterval === 0)? labels[pos] : ''}</span>
                <span className="sc-value">{item}</span>
                <div className="sc-tooltip">
                  <span className="sc-tooltip-value">{tooltips[pos]}</span>
                </div>
              </div>
            )
          })
          }
        </div>
      </div>
    )
  }

  return (
    <div>
      {(() => {
        switch (config.type) {
          case 'bar':
            return <BarChart />
          default:
          case 'column':
            return <ColumnChart />
        }
      })()}
    </div>
    );
  }

  export default SimpleChart;