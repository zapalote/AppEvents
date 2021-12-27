// from https://github.com/ajwann/svg-loaders-react

const LoadingIndicator = (props) => {

    const { width = 60, coverall = true, fill = '#fff', className = '' } = props;
    return ( 
        <div style={Object.assign({}, spinner, coverall && overlay)}>
          <svg width={width} height={width} stroke="#000" viewBox="0 0 60 60" fill={fill} className={className} >
            <g
              transform="translate(1 1)"
              strokeWidth={2}
              fill="none"
              fillRule="evenodd"
            >
              <circle strokeOpacity={0.5} cx={18} cy={18} r={18} />
              <path d="M36 18c0-9.94-8.06-18-18-18">
                <animateTransform
                  attributeName="transform"
                  type="rotate"
                  from="0 18 18"
                  to="360 18 18"
                  dur="1s"
                  repeatCount="indefinite"
                />
              </path>
            </g>
          </svg>
        </div>
    );
  };

  const spinner = {
    width: "100%",
    height: "10rem",
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