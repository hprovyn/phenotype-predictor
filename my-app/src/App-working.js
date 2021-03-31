import logo from './logo.svg';
import './App.css';
import Avatar from 'avataaars'; 
function App() {
                        const params = window.location.search
			const parsedParams = new URLSearchParams(params)
	const options = {'topType':'LongHairStraight'}
			for (const entry of parsedParams.entries()) {
			options[entry[0]] = entry[1]
			}
  return (
    <div className="App">
      <header className="App-header">
        <img src={logo} className="App-logo" alt="logo" />
        <p>
          Custom Avatars for YSEQ
        </p>
	  <Avatar
	  style={{width: '100px', height: '100px'}}
	  avatarStyle='Circle'
	  topType={options['topType']}
	  facialHairType='Blank'
	  clotheType='Hoodie'
	  clotheColor='PastelBlue'
	  eyeType='Happy'
	  eyebrowType='Default'
	  mouthType='Smile'
	  skinColor='Light'
	  />
      </header>
    </div>
  );
}

export default App;
