// Aadapted from https://www.smashingmagazine.com/2020/03/sortable-tables-react/
// by Kristofer Giltvedt Selbekk 

import React, { useState, useEffect, useMemo } from 'react';
import { useNavigate } from 'react-router';
import Sortable from './sort-light.png'

const useSortableData = (items, config = null) => {
  const [sortConfig, setSortConfig] = useState(config);

  const sortedItems = useMemo(() => {
    let sortableItems = [...items];
    if (sortConfig !== null) {
      sortableItems.sort((a, b) => {
        const n = a[sortConfig.key];
        const m = b[sortConfig.key];
        if (n < m) {
          return sortConfig.direction === 'asc' ? -1 : 1;
        }
        if (n > m) {
          return sortConfig.direction === 'asc' ? 1 : -1;
        }
        return 0;
      });
    }
    return sortableItems;
  }, [items, sortConfig]);

  const requestSort = (key) => {
    let direction = 'asc';
    if (
      sortConfig &&
      sortConfig.key === key &&
      sortConfig.direction === 'asc'
    ) {
      direction = 'desc';
    }
    setSortConfig({ key, direction });
  };

  return { rows: sortedItems, requestSort, sortConfig };
};

const StatsTable = (props) => {
  const { headings, sortable, data, drill, popupLinks, rowsep } = props;
  const { rows, requestSort, sortConfig } = useSortableData(data);
  const navigate = useNavigate();

  const getClassNamesFor = (name) => {
    if (!sortConfig) {
      return;
    }
    return sortConfig.key === name ? sortConfig.direction : undefined;
  };

  const drillTo = (link) => {
    navigate(link);
  }

  const popUp = (ip) => {
    props.onPopUp && props.onPopUp(ip);
  }

  const computeTotals = (table, cols) => {
    if(cols === 0) return [];
    let sums = new Array(cols).fill(0);
    const notNumber = /[^\d]/g;
    table.forEach((row) => {
      row.forEach((col, i) => {
        const n = parseInt(col);
        if (isNaN(n) || (typeof col === 'string' && col.search(notNumber) >= 0)) {
          sums[i] = '';
        } else {
          sums[i] += n;
        }
      })
    });
    return sums.slice(1)
  }

  const nCols = (data[0] && Object.keys(data[0]).length) || 0;
  const nRows = (data[0] && data.length) || 0;
  const totals = computeTotals(data, nCols);
  const [sortKeys, setSortKeys] = useState([]);
  useEffect(() => {
    let keys = [];
    for(let i=0; i < nCols; i++) {
      keys[i] = i;
    }
    setSortKeys(keys);
  }, [nCols]);

  console.log('render table', sortable);
  return (
    <>
      <table id='stats' className='sttable'>
        <thead>
          <tr>
            {headings.map((heading, idx) => {
              return sortable && sortable[idx] ? 
                <th key={"th" + idx} onClick={() => requestSort(sortKeys[idx])}
                  className={getClassNamesFor(sortKeys[idx])}><img className='sortableicon' src={Sortable} alt="sortable" />{heading}
                </th> : 
                <th key={"th" + idx}>{heading}</th>
            }
            )}
          </tr>
        </thead>
        <tbody>
          {rows.map((row, rx) =>(
            <tr key={'tr' + rx} 
                className={((rowsep && rowsep[rx] === 1)? 'sep' : '')} 
                onClick={() => (drill)? drillTo(drill[rx]) : popupLinks && popUp(popupLinks[rx]) }>
              {row.map((item, ix) => (
                <td key={'td'+ix}>{item}</td>
              ))}
            </tr>
          ))}
        </tbody>
        {nRows ? (
          <tfoot>
            <tr>
              <td>{nRows} <span style={{color: '#aaa'}}>rows</span></td>
              {totals.map((td, tx) => (<td key={'f'+tx}>{td}</td>))}
            </tr>
          </tfoot>
        ) : null}
      </table>
    </>
  );
};

export default StatsTable;

// Usage:
// 
//       <StatsTable
//         headings={['Name','Price','In Stock']}
//         data={
//           ['Cheese', 4.9, 20],
//           ['Milk', 1.9, 32],
//           ['Sour Cream ', 2.9, 86],
//           ['Fancy French Cheese 🇫🇷', 99, 12]
//         }
//       />
