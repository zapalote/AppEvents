
// anonimize the IP address, non-reversable, KISS method
const anonIP = (ip) => {
  const isIP6 = ip.search(":") >= 0;
  const parts = ip.split(/[:.]/);
  if(parts.length < 4) return 1;

  let m = 0;
  for(let j = 0; j < 4; j++){
    const p = (parts[j])? parts[j] : 0;
    const a = (isIP6) ? parseInt(p, 16) : p;
    m += parseInt(a) + (j * 255);
  }
  return m;
}

export { anonIP };