import logo from './logo.svg';
import './App.css';
import Avatar from 'avataaars'; 
function App() {
                        const params = window.location.search
			const parsedParams = new URLSearchParams(params)
	const options = {'topType':'LongHairStraight',
	                 'skinColor': 'Light',
	                 'hairColor': 'Brown',
		         'freckleType': 'Default'}
			for (const entry of parsedParams.entries()) {
			options[entry[0]] = entry[1]
			}
	var eyeOrAccessoryKey = null
	if (options.hasOwnProperty('accessoriesType')) {
  return (
	  <Avatar
	  style={{width: '200px', height: '200px'}}
	  avatarStyle='Circle'
	  topType={options['topType']}
	  facialHairType='Blank'
	  clotheType='ShirtCrewNeck'
	  clotheColor='PastelBlue'
	  accessoriesType={options['accessoriesType']}
	  eyebrowType='Default'
	  mouthType='Smile'
	  yseqlogoType='Heart'
	  hairColor={options['hairColor']}
	  skinColor={options['skinColor']}
	  freckleType={options['freckleType']}
	  />
  );
	}

	if (options.hasOwnProperty('eyeType')) {
  return (
	  <Avatar
	  style={{width: '200px', height: '200px'}}
	  avatarStyle='Circle'
	  topType={options['topType']}
	  facialHairType='Blank'
	  clotheType='ShirtCrewNeck'
	  clotheColor='PastelBlue'
	  eyeType={options['eyeType']}
	  eyelashType={options['eyelashType']}
	  eyebrowType='Default'
	  mouthType='Smile'
	  yseqlogoType='Heart'
	  hairColor={options['hairColor']}
	  skinColor={options['skinColor']}
	  freckleType={options['freckleType']}
	  />
  );
	}

}

export default App;
