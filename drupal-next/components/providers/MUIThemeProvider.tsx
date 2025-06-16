"use client"

import { createTheme, ThemeProvider } from "@mui/material"
import { ThemeOptions } from "@mui/material/styles"
import { ReactNode } from "react"

export const themeOptions: ThemeOptions = {
  palette: {
    mode: "light",
    primary: {
      main: "#d6d6e8",
    },
    secondary: {
      main: "#ea0054",
    },
    error: {
      main: "#7d1313",
    },
    divider: "rgba(10,48,232,0.12)",
    background: {
      default: "#f3f3ae",
      paper: "#d6f1b9",
    },
    text: {
      primary: "rgba(65,32,32,0.87)",
      secondary: "rgba(31,30,53,0.6)",
      disabled: "rgba(94,92,92,0.38)",
      hint: "#321ba4",
    },
  },
  typography: {
    fontSize: 20,
    htmlFontSize: 21,
    button: {
      fontSize: "2.1rem",
      letterSpacing: "0.21em",
      fontFamily: "Droid Serif",
    },
    overline: {
      fontWeight: 500,
      fontSize: "1.6rem",
      letterSpacing: "0.25em",
    },
    h1: {
      fontSize: "6.5rem",
      lineHeight: 1.27,
      fontWeight: 400,
      letterSpacing: "0.14em",
    },
    h2: {
      fontWeight: 400,
      lineHeight: 1.4,
      fontSize: "4.5rem",
      letterSpacing: "0.11em",
    },
    h3: {
      fontSize: "3.7rem",
      fontWeight: 500,
      lineHeight: 1.36,
      letterSpacing: "0.14em",
    },
    h4: {
      fontSize: "2.6rem",
      fontWeight: 500,
      lineHeight: 1.5,
      letterSpacing: "0.16em",
    },
    h5: {
      fontSize: "2.1rem",
      fontWeight: 500,
      lineHeight: 1.47,
      letterSpacing: "0.16em",
    },
    h6: {
      fontSize: "1.7rem",
      fontWeight: 600,
      lineHeight: 1.87,
      letterSpacing: "0.19em",
    },
    subtitle1: {
      fontWeight: 1000,
      lineHeight: 2.76,
      letterSpacing: "1.5em",
    },
    subtitle2: {
      lineHeight: 3,
      fontWeight: 1000,
      letterSpacing: "1.04em",
    },
    body1: {
      fontSize: "1.7rem",
      fontWeight: 600,
      lineHeight: 1.72,
      letterSpacing: "0.16em",
    },
    body2: {
      fontSize: "1.2rem",
      fontWeight: 600,
      lineHeight: 1.72,
      letterSpacing: "0.16em",
    },
  },
  components: {
    MuiTypography: {
      variants: [{ props: { variant: "body2" }, style: { fontSize: 12 } }],
    },
  },
}

const theme = createTheme(themeOptions)

export default function MUIThemeProvider({
  children,
}: {
  children: ReactNode
}) {
  return <ThemeProvider theme={theme}>{children}</ThemeProvider>
}
