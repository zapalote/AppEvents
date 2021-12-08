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
        if (a[sortConfig.key] < b[sortConfig.key]) {
          return sortConfig.direction === 'asc' ? -1 : 1;
        }
        if (a[sortConfig.key] > b[sortConfig.key]) {
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
  const { headings, data, drill, popupLinks, rowsep } = props;
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
    let totals = new Array(cols).fill(0);
    const notNumber = /[^\d]/g;
    table.forEach((row) => {
      row.forEach((col, i) => {
        if (typeof col === 'string' && col.search(notNumber) >= 0) {
          totals[i] = '';
        } else {
          totals[i] += parseInt(col);
        }
      })
    });
    return totals.slice(1)
  }

  const nCols = (data[0] && Object.keys(data[0]).length) || 0;
  const totals = computeTotals(data, nCols);
  const [sortKeys, setSortKeys] = useState([]);
  useEffect(() => {
    let keys = [];
    for(let i=0; i < nCols; i++) {
      keys[i] = i;
    }
    setSortKeys(keys);
  }, [nCols]);

  return (
    <>
      <table id='stats' className='sttable'>
        <thead>
          <tr>
            {headings.map((heading, idx) => (
              <th key={"th" + idx} onClick={() => requestSort(sortKeys[idx])}
                className={getClassNamesFor(sortKeys[idx])}><img className='sortable-icon' src={Sortable} alt="sortable" />{heading}
              </th>
              )
            )}
          </tr>
        </thead>
        <tbody>
          {rows.map((row, rx) =>(
            <tr key={'tr' + rx} className={((rowsep && rowsep[rx] == 1)? 'sep' : '')} 
              onClick={() => (drill)? drillTo(drill[rx]) : popUp(popupLinks[rx])}>
              {row.map((item, ix) => (
                <td key={'td'+ix}>{item}</td>
              ))}
            </tr>
          ))}
        </tbody>
        {totals[0] ? (
          <tfoot>
            <tr>
              <td>Totals</td>
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
