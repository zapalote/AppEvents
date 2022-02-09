import React, { useState, useLayoutEffect, useRef } from 'react';
import { useSite } from '../contexts';
import Sortable from './sort-light.png'

const PulldownMenu = (props) => {

  const sites = ['balance', 'inspira', 'eft', 'zapalote', 'zoe'];
  const { currentSite, setCurrentSite } = useSite();

  const [open, setOpen] = useState(false);
  const ref = useRef();

  // close dropdown if clicked anywhere outside of dropdown
  // on initial render, add click event listener
  useLayoutEffect(() => {
    const onBodyClick = (event) => {
      // check if element that was clicked is inside of ref'd component
      // if so no action is required from this event listener so exit
      if (!ref.current || ref.current.contains(event.target)) {
        return;
      }
      // else close the dropdown
      setOpen(false);
    };
    document.body.addEventListener("click", onBodyClick);

    // CLEANUP, return a function from useEffect, this is called before comp
    // remove event listener
    return () => {
      document.body.removeEventListener("click", onBodyClick);
    };
  }, []);

  const onNew = (site) => {
    setCurrentSite(site);
  }


  // <div class="dropdown">
  //   <button class="dropbtn">Dropdown</button>
  //   <div class="dropdown-content">
  //     <a href="#">Link 1</a>
  //     <a href="#">Link 2</a>
  //     <a href="#">Link 3</a>
  //   </div>
  // </div>

  return (
    <div className="dropdown">
      <div ref={ref} className="dropbtn" onClick={() => setOpen(!open)}>▽</div>
      <ul className={`dropdown-content ${open ? 'shown' : 'hidden'}`}>
        {
          sites.map((site, idx) => {
            return <li key={'m' + idx} onClick={() => onNew(site)}>{site}</li>
          })
        }
      </ul>
    </div>
  );
}

export default PulldownMenu;