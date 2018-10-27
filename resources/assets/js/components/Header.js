import React from "react";
import { NavLink } from "react-router-dom";

export default class Header extends React.Component {
  render() {
    return (
      <div className="header clearfix">
        <nav>
          <ul className="nav nav-pills float-right">
            <li className="nav-item" role="presentation">
              <NavLink exact to="/" className="nav-link">
                Home
              </NavLink>
            </li>
            <li className="nav-item" role="presentation">
              <NavLink to="/create" className="nav-link">
                Maak iCal
              </NavLink>
            </li>
          </ul>
        </nav>
        <h3 className="text-muted">UHasselt Personal Calendars</h3>
      </div>
    );
  }
}
