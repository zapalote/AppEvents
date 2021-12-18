import { useEffect, useState } from "react";

const IPpopUp = (props) => {
  const { data } = props;
  const [show, setShow] = useState(false);

  const closeHandler = (e) => {
    setShow(false);
    props.onClose && props.onClose();
  };

  useEffect(() => {
    setShow(props.show);
  }, [props.show]);

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

export default IPpopUp;