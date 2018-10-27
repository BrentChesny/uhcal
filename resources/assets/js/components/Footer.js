import React from "react";

import API_URL from "../CalendarAPI";

export default class Footer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      count: 0
    };
  }

  componentDidMount() {
    fetch(API_URL + "/calendars/count")
      .then(response => response.json())
      .then(data => this.setState(data));
  }

  render() {
    const year = new Date().getFullYear();
    return (
      <footer className="footer">
        <p className="left">&copy; {year} Brent Chesny</p>
        <p className="right">&hearts; {this.state.count} calendars created</p>
      </footer>
    );
  }
}
