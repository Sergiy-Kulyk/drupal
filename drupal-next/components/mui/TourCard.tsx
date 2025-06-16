import AccessTimeIcon from '@mui/icons-material/AccessTime'
import { Box, Paper, Rating, Typography } from '@mui/material'
import { useState } from 'react'

const TourCard = ({ tour }) => {
  const [value, setValue] = useState<number | null>(tour.rating)
  return (
    <Paper elevation={10} sx={{ overflow: 'hidden' }}>
      <img src={tour.image} alt='image' style={{ width: '100%' }} />
      <Box paddingX={1}>
        <Typography variant='h6' component={'h2'}>
          {tour.name}
        </Typography>
        <Box
          sx={{
            display: 'flex',
            gap: '8px',
            justifyContent: 'start',
            alignItems: 'center',
          }}
        >
          <AccessTimeIcon sx={{ width: '20px' }} />

          <Typography variant='body2' component={'p'}>
            Time: {tour.duration}
          </Typography>
        </Box>
        <Box
          sx={{
            display: 'flex',
            justifyContent: 'start',
            alignItems: 'center',
            gap: '10px',
          }}
        >
          <Rating
            name='simple-controlled'
            value={value}
            onChange={(event, newValue) => {
              setValue(newValue)
            }}
            readOnly
            precision={0.1}
            size='small'
          />
          <Typography variant='body2' component={'p'}>
            {value} (Reviews: {tour.numberOfReviews})
          </Typography>
        </Box>
        <Box>
          <Typography variant='h6' component={'h3'}>
            Price: {tour.price}$
          </Typography>
        </Box>
      </Box>
    </Paper>
  )
}

export default TourCard
