import { Box } from '@mui/material'

function MuiResponsiveness() {
  return (
    <Box
      sx={{
        height: '300px',
        width: {
          xs: 50,
          sm: 100,
          md: 300,
          lg: 400,
          xl: 500,
        },
      }}
    ></Box>
  )
}

export default MuiResponsiveness
