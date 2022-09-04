const nl2br = (str) => {
  var res = str.replace(/\r\n/g, "<br>");
  res = res.replace(/(\n|\r)/g, "<br>");
  return res;
}

const getToday = () => {
  const today = new Date();
  const yyyy = today.getFullYear();
  const mm = ("00"+(today.getMonth()+1)).slice(-2);
  const dd = ("00"+today.getDate()).slice(-2);
  return yyyy+'-'+mm+'-'+dd;
}

export { nl2br, getToday }