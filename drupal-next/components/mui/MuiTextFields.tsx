import { Stack, TextField } from '@mui/material'
import React from 'react'

const MuiTextFields = () => {
  return (
    <Stack spacing={4}>
      <Stack direction={'row'} spacing={'2'}>
        <TextField label='name' />
      </Stack>
    </Stack>
  )
}

export default MuiTextFields
