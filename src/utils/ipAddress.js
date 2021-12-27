
// anonimize the IP address, non-reversable, KISS method
const anonIP = (ip) => {
  const isIP6 = ip.search(":") >= 0;
  const parts = ip.split(/[:.]/);
  if(parts.length < 4) return 1;

  let m = 0;
  for(let j = 0; j < 4; j++){
    const a = (isIP6) ? parseInt(parts[j], 16) : parts[j];
    m += parseInt(a) + (j * 255);
  }
  return m;
}

export { anonIP };