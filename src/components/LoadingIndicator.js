import * as SVGLoaders from 'svg-loaders-react';

const LoadingIndicator = (props) => {

    const { width = 40, coverall = true, fill = '#fff' } = props;
    return ( 
        <div style={Object.assign({}, spinner, coverall && overlay)}>
           <SVGLoaders.Grid width={width} fill={fill}/>
        </div>
    );
  };

  const spinner = {
    width: "100%",
    height: "100%",
    display: "flex",
    justifyContent: "center",
    alignItems: "center",
    zIndex: 999
  }
  const overlay = {
    position: 'fixed',
    top: 0,
    left: 0,
    backgroundColor: "#000",
    opacity: 0.7
  }

  export default LoadingIndicator;