
// anonimize the IP address, non-reversable, KISS method
const anonIP = (ip) => {
  const isIP6 = ip.search(":") >= 0;
  const parts = ip.split(/[:.]/);
  if(parts.length < 4) return 1;

  let m = 0;
  let j = 0;
  for (const i of parts) {
    if(j > 4) return m;
    const a = (isIP6) ? parseInt(i, 16) : i;
    m += parseInt(a) + (j++ * 255);
  }
  return m;
}

export { anonIP };