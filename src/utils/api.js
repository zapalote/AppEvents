import axios from 'axios';
axios.defaults.baseURL = (process.env.NODE_ENV === 'development')?
  'http://localhost/AppEvents/public/statsData.php' : 'statsData.php';

const sendApi = async (params) => {

  return axios.request(params)
    .then(res => res.data)
    .catch(err => err.response.data);
};

const getApi = async (params) => {

  return axios.request(params)
    .then(res => res.data);
}

export { sendApi, getApi };