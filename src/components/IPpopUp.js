import React, { useLayoutEffect, useEffect, useState } from "react";

const IPEvents = (props) => {
  const { data } = props;
  const [show, setShow] = useState(false);

  const closeHandler = (e) => {
    setShow(false);
    props.onClose && props.onClose();
  };

  useLayoutEffect(() => {
    setShow(props.show);
  }, [props.show]);

  useEffect(() => {
    // click anywhere will close the popup
    document.body.addEventListener("click", closeHandler);

    return () => {
      document.body.removeEventListener("click", closeHandler);
    };
  }, []);

  // possible data.type values are: 'desktop', 'mobile' and 'robot'
  return (
    <>
      {show ?
        <table className='popdata sttable popup' onClick={closeHandler}>
          <thead><tr><th>#{data.session} <span className='close'>&times;</span></th></tr></thead>
          <tbody>
            {data.events.map((row, rx) => (
              <tr key={'pop'+rx}><td className={data.type[rx]}>{row}</td></tr>
            ))}
          </tbody>
        </table>
        : null
      }
    </>
  );
};

export const IPpopUp = React.memo(IPEvents);