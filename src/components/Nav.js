import React, { useState, useLayoutEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { GiHamburgerMenu } from 'react-icons/gi'

const Nav = (props) => {

  const { active = '24 hrs' } = props;
  const navigate = useNavigate();
  const [isMobile, setIsMobile] = useState();
  const actions = [
    { 'goto': '/', 'label': '24 hrs' },
    { 'goto': '/30', 'label': '30 days' },
    { 'goto': '/m', 'label': 'monthly' },
    { 'goto': '/ref', 'label': 'referrals' },
    { 'goto': '/s', 'label': 'topics' }
  ];

  useLayoutEffect(() => {
    setIsMobile(window.innerWidth <= 480);
  }, []);

  const HamburgerMenu = () => {
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

    return (
      <div className="dropdown hamburger">
        <div ref={ref} className="dropbtn" onClick={() => setOpen(!open)}>
          <GiHamburgerMenu />
        </div>
        <ul className={`dropdown-content ${open ? 'shown' : 'hidden'}`}>
          {
            actions.map((opt, idx) => {
                return (
                  opt.label !== active && <li key={'m' + idx} onClick={() => navigate(opt.goto)}>{opt.label}</li>
                );
            })
          }
        </ul>
      </div>
    );

  }

  const HorizontalNav = () => {
    return (
      <div className="btns">
        {actions.map((opt, idx) => {
          return (
            opt.label !== active && <div key={'a'+idx} onClick={() => navigate(opt.goto)} className="button" >
              {opt.label}</div>
            );
          })
        }
      </div>
    );
  }

  return (
    <>
      {isMobile ?  <HamburgerMenu /> : <HorizontalNav /> }
    </>
  )
}

export default Nav;