"use client"

import { Button, Typography, Paper } from "@mui/material"

export default function TestMUI() {
  return (
    <Paper sx={{ p: 3 }}>
      <Typography variant="h1" gutterBottom>
        MUI Theme Test
      </Typography>
      <Typography variant="body1" paragraph>
        This is a test page to verify that the MUI theme is working correctly.
      </Typography>
      <Button variant="contained" color="primary">
        Primary Button
      </Button>
      <Button variant="contained" color="secondary" sx={{ ml: 2 }}>
        Secondary Button
      </Button>
    </Paper>
  )
}
