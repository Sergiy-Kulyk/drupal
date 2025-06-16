import { Typography } from '@mui/material'

export const MuiTypography = () => {
  return (
    <div>
      <Typography variant='h1'>Text h1</Typography>
      <Typography variant='h2'>Text h2</Typography>
      <Typography variant='h3'>Text h3</Typography>
      <Typography variant='h4'>Text h4</Typography>
      <Typography variant='h5'>Text h5</Typography>
      {/* Apply margin bottom */}
      <Typography variant='h6' gutterBottom>
        Text h6
      </Typography>

      {/* Apply h4 styles to the h1 component */}
      <Typography variant='h4' component='h1'>
        Text h1 with h4 styles
      </Typography>
      <Typography variant='subtitle1'>Text subtitle1</Typography>
      <Typography variant='subtitle2'>Text subtitle2</Typography>
      <Typography variant='body1'>
        Lorem ipsum dolor sit amet consectetur, adipisicing elit. Reiciendis,
        vel eaque magnam consectetur odit nemo aperiam nam. Sunt, odio
        architecto. Dignissimos nisi deleniti magnam sequi optio non, corrupti
        ipsam in.
      </Typography>
      <Typography variant='body2'>
        Lorem ipsum, dolor sit amet consectetur adipisicing elit. Nobis facilis
        porro natus quisquam magni labore earum corporis error ea ut. Unde
        officiis corporis explicabo aperiam, quisquam perspiciatis tempore
        ratione. Debitis?
      </Typography>
    </div>
  )
}
